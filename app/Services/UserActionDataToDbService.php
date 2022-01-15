<?php

namespace App\Services;

use App\Common\Enums\ReportStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomQueue;
use App\Common\Tools\CustomRedis;
use App\Enums\QueueEnums;
use App\Enums\UserActionTypeEnum;
use App\Models\UserActionLogModel;

class UserActionDataToDbService extends BaseService
{

    protected $queueEnum;



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

        $queue->openTransaction();
        $queue->setConsumeHook(function ($data) use ($productService){
            $product = $productService->readByMap($data['cp_type'],$data['cp_product_alias']);
            $data['product_id'] = $product['id'];
            $data['status'] = ReportStatusEnum::WAITING;
            $data['matcher'] = $product['matcher'];


            if($data['type'] == UserActionTypeEnum::ADD_SHORTCUT){
                $keyArr = ['user_add_shortcut_log',$data['open_id'],$data['product_id'],$data['type'],$data['cp_channel_id'],$data['action_id']];
                $key = implode(':',$keyArr);

                $customRedis = new CustomRedis();
                $info = $customRedis->get($key);

                if(!empty($info)){
                    echo "重复加桌\n";
                    return;
                }

                (new UserActionLogModel())
                    ->setTableNameWithMonth($data['action_time'])
                    ->create($data);
                $customRedis->set($key,1);
                $customRedis->expire($key,7200);

                return;
            }

            (new UserActionLogModel())
                ->setTableNameWithMonth($data['action_time'])
                ->create($data);
        });

        $queue->setExceptionHook(function ($data,$e){
            if($e->getCode() == 23000){
                echo "  命中唯一索引 \n";
            }
        });

        $queue->consume();
    }



}
