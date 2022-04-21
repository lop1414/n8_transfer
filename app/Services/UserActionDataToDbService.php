<?php

namespace App\Services;

use App\Common\Enums\ReportStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomQueue;
use App\Enums\QueueEnums;
use App\Enums\UserActionTypeEnum;
use App\Models\UserActionLogModel;
use App\Traits\UserAction\AddShortcut;

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

        $queue->openTransaction();
        $queue->setConsumeHook(function ($data) use ($productService){
            $product = $productService->readByMap($data['cp_type'],$data['cp_product_alias']);
            $data['product_id'] = $product['id'];
            $data['status'] = ReportStatusEnum::WAITING;
            $data['matcher'] = $product['matcher'];


            if($data['type'] == UserActionTypeEnum::ADD_SHORTCUT){
                if($this->isRepeatAddShortcut($data)){
                    echo "重复加桌\n";
                    return;
                }
            }

            (new UserActionLogModel())
                ->setTableNameWithMonth($data['action_time'])
                ->create($data);

            // 加桌记录log
            if($data['type'] == UserActionTypeEnum::ADD_SHORTCUT){
                $this->setAddShortcutCacheLog($data);
            }
        });

        $queue->setExceptionHook(function ($data,$e){
            if($e->getCode() == 23000){
                echo "  命中唯一索引 \n";
            }
        });

        $queue->consume();
    }



}
