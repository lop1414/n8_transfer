<?php

namespace App\Services;

use App\Common\Enums\ReportStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Common\Tools\CustomQueue;
use App\Enums\QueueEnums;
use App\Models\UserActionLogModel;
use App\Traits\UserAction\AddShortcut;
use Illuminate\Support\Facades\DB;

class UserActionDataToDbService extends BaseService
{

    protected $queueEnum;
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
                $product = $productService->readByMap($data['cp_type'],$data['cp_product_alias']);
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
                $data['exception'] = $e->getErrorInfo();
                $data['code'] = $e->getCode();
                $rePushData[] = $data;

            }catch (\Exception $e){
                DB::rollBack();

                if($e->getCode() == 23000){
                    echo "  命中唯一索引 \n";
                    continue;
                }

                //日志
                (new ErrorLogService())->catch($e);

                // 重回队列
                $data['exception'] = $e->getMessage();
                $data['code'] = $e->getCode();
                $rePushData[] = $data;

            }
        }

        foreach($rePushData as $item){
            // 重推
            $queue->setItem($item);
            $queue->rePush();
        }
    }



}
