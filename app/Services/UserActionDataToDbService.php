<?php

namespace App\Services;

use App\Common\Enums\ReportStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;
use App\Common\Tools\CustomQueue;
use App\Common\Tools\CustomRedis;
use App\Enums\QueueEnums;
use App\Models\UserActionLogModel;
use App\Traits\UserAction\AddShortcut;
use Illuminate\Support\Facades\DB;

class UserActionDataToDbService extends BaseService
{

    protected $queueEnum;

    /**
     * @var CustomRedis
     */
    protected $customRedis;

    use AddShortcut;



    public function setQueueEnum($queueEnum){
        Functions::hasEnum(QueueEnums::class,$queueEnum);
        $this->queueEnum = $queueEnum;
        return $this;
    }



    /**
     * @return mixed
     * 获取队列枚举
     */
    public function getQueueEnum(){
        return $this->queueEnum;
    }



    public function run(){

        $queue = new CustomQueue($this->queueEnum);

        $productService = (new ProductService())->setMap();

        $rePushData = [];
        while ($data = $queue->pull()) {

            try{
                DB::beginTransaction();
                if(empty($data['cp_product_alias']) && !empty($data['cp_channel_id'])){
                    $channel = $this->readChannelByCpChannelId($data['cp_type'],$data['cp_channel_id']);
                    if(empty($channel)){
                        throw new CustomException([
                            'code' => 'NOT_FOUND_CHANNEL',
                            'message' => '找不到渠道',
                            'log'   => true,
                            'data' => $data,
                        ]);
                    }
                    $product = $productService->read($channel['product_id']);
                    var_dump($product);
                }else{
                    $product = $productService->readByMap($data['cp_type'],$data['cp_product_alias']);
                }
                $data['product_id'] = $product['id'];
                $data['status'] = ReportStatusEnum::WAITING;
                $data['matcher'] = $product['matcher'];

                (new UserActionLogModel())
                    ->setTableNameWithMonth($data['action_time'])
                    ->create($data);

                DB::commit();

            }catch (CustomException $e){
                DB::rollBack();

                //日志
                (new ErrorLogService())->catch($e);

                // 重回队列
                $queue->item['exception'] = $e->getMessage();
                $queue->item['code'] = $e->getCode();
                $rePushData[] = $queue->item;

            }catch (\Exception $e){
                DB::rollBack();

                if($e->getCode() == 23000){
                    echo "  命中唯一索引 \n";
                    continue;
                }

                //日志
                (new ErrorLogService())->catch($e);

                // 重回队列
                $queue->item['exception'] = $e->getMessage();
                $queue->item['code'] = $e->getCode();
                $rePushData[] = $queue->item;

            }
        }

        foreach($rePushData as $item){
            // 重推
            $queue->setItem($item);
            $queue->rePush();
        }
    }


    public function getCustomRedis(): CustomRedis
    {
        if(empty($this->customRedis)){
            $this->customRedis = new CustomRedis();
        }
        return $this->customRedis;
    }



    public function readChannelByCpChannelId($cpType,$cpChannelId): array
    {
        $customRedis = $this->getCustomRedis();
        $key = 'channel:'.$cpType.':'.$cpChannelId;
        $info = $customRedis->get($key);

        $ttl = 0;
        if($info === false){
            $channels = (new UnionApiService())->apiGetChannel(['cp_channel_id' => $cpChannelId]);
            foreach ($channels as $channel){
                // 设置缓存
                $ret = $customRedis->set($key, $channel);
                if($ttl > 0){
                    $customRedis->expire($key, $ttl);
                }
            }
            $info = $customRedis->get($key);
        }

        return empty($info) ? [] : $info;
    }



}
