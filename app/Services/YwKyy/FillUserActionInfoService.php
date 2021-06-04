<?php
/**
 * 补充用户行为信息 临时脚本
 */
namespace App\Services\YwKyy;


use App\Common\Enums\ReportStatusEnum;
use App\Common\Services\BaseService;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Models\UserActionLogModel;
use App\Sdks\Yw\YwSdk;
use App\Services\ProductService;
use App\Services\UserActionBaseService;


class FillUserActionInfoService extends BaseService
{

    protected $product;

    protected $ywSdk;

    public function setProduct($product){
        $this->product = $product;
        $this->product['cp_account'] = (new ProductService())->readCpAccount($this->product['cp_account_id']);
        $this->ywSdk = new YwSdk($this->product['cp_product_alias'],$this->product['cp_account']['account'],$this->product['cp_account']['cp_secret']);
        return $this;
    }


    /**
     * 渠道
     * @param $startTime
     * @param $endTime
     */
    public function cpChannelId($startTime,$endTime){
        $time = $startTime;
        $userActionLogModel = new UserActionLogModel();
        $service = new UserActionBaseService();
        $service->setProduct($this->product);
        $service->setActionType(UserActionTypeEnum::REG);
        $service->setSource(DataSourceEnums::CP);
        while($time < $endTime){
            $tmpEndTime = date('Y-m-d H:i:s',  strtotime($time) + 60*60);
            $tmpEndTime = min($tmpEndTime,date('Y-m-d H:i:s'));

            echo("时间 : {$time} ~ {$tmpEndTime} \n");

            $para = [
                'start_time' => strtotime($time),
                'end_time'  => strtotime($tmpEndTime),
                'page'      => 1
            ];
            $currentCount = 0;
            $unionApiService = new UnionApiService();
            do{
                $tmp = $this->ywSdk->getUser($para);
                $count = $tmp['total_count'];
                $currentCount += count($tmp['list']);
                foreach($tmp['list'] as $user){
                    try{
                        $tmpUser = $userActionLogModel
                            ->setTableNameWithMonth($user['reg_time'])
                            ->where('open_id',$user['guid'])
                            ->where('type',UserActionTypeEnum::REG)
                            ->get();

                        //没有渠道
                        $cpChannelId = empty($user['channel_id']) ? '': $user['channel_id'];
                        if(empty($cpChannelId)) continue;

                        $channel = $unionApiService->apiReadChannel([
                            'product_id'    => $this->product['id'],
                            'cp_channel_id' => $cpChannelId
                        ]);

                        foreach ($tmpUser as $modelUser){
                            if(!empty($modelUser->cp_channel_id)) continue;

                            // 渠道创建时间 大于 注册时间
                            if($channel['create_time'] > $modelUser->action_time){
                                echo "渠道创建时间 大于 注册时间:".$modelUser->open_id. "\n";
                                continue;
                            }
                            $modelUser->cp_channel_id = $user['channel_id'];
                            $modelUser->save();
                            echo "渠道更新:".$modelUser->open_id. "\n";
                        }
                    }catch(CustomException $e){
                        (new ErrorLogService())->catch($e);

                        //日志
                        $errorInfo = $e->getErrorInfo(true);
                        echo $errorInfo['message']. "\n";

                    }catch (\Exception $e){

                        //未命中唯一索引
                        if($e->getCode() != 23000){
                            //日志
                            (new ErrorLogService())->catch($e);
                            echo $e->getMessage()."\n";
                        }else{
                            echo "  命中唯一索引 \n";
                        }

                    }
                }
                $para['page'] += 1;
                $para['next_id'] = $tmp['next_id'];
            }while($currentCount < $count);


            $time = $tmpEndTime;
        }
    }




    /**
     * 注册时间
     */
    public function actionTime(){
        $list = (new UserActionLogModel())
            ->setTableNameWithMonth('1970-01-01')
            ->where('product_id',$this->product['id'])
            ->where('type',UserActionTypeEnum::REG)
            ->get();

        foreach ($list as $item){
            $tmp = $this->ywSdk->getUser([
                'guid'  => $item['open_id']
            ]);
            if(!empty($tmp['list'])){
                $user = $tmp['list'][0];
                //注册时间
                (new UserActionLogModel())->setTableNameWithMonth($user['reg_time'])->create([
                    'product_id'    => $item['product_id'],
                    'open_id'       => $item['open_id'],
                    'action_time'   => $user['reg_time'],
                    'type'          => $item['type'],
                    'cp_channel_id' => $item['type'],
                    'request_id'    => $item['request_id'],
                    'ip'            => $item['ip'],
                    'data'          => $item['data'],
                    'status'        => ReportStatusEnum::WAITING,
                    'action_id'     => $item['action_id'],
                    'matcher'       => $item['matcher'],
                ]);
                $item->delete();

            }
        }
    }





}
