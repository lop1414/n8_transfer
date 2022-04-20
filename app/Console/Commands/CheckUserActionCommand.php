<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Helpers\Functions;
use App\Enums\UserActionTypeEnum;
use App\Services\UserAction\UserActionInterface;
use App\Services\UserActionService;
use Illuminate\Container\Container;

class CheckUserActionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'check_user_action {--cp_type=} {--product_type=} {--time=} {--time_interval=} {--product_id=}';


    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '监测用户行为数据';



    /**
     * @var
     * 时间间隔
     */
    protected $timeInterval = 60*30;



    public function handle(){
        $actionType = $this->option('action_type');
        $actionType && Functions::hasEnum(UserActionTypeEnum::class, $actionType);

        $cpType = $this->option('cp_type');
        $cpType && Functions::hasEnum(CpTypeEnums::class, $cpType);

        // 时间参数
        $time = $this->option('time');
        list($startTime,$endTime) = explode(",", $time);
        $endTime = min($endTime,date('Y-m-d H:i:s'));
        Functions::checkTimeRange($startTime,$endTime);

        // 时间区间
        $timeInterval = $this->option('time_interval') ?? $this->timeInterval;

        //产品
        $productType = $this->option('product_type');
        $productType && Functions::hasEnum(ProductTypeEnums::class, $productType);
        $productId = $this->option('product_id');


        $container = Container::getInstance();
        $services = UserActionService::getNeedCheckDiffService();
        foreach ($services as $service){
            $container->bind(UserActionInterface::class,$service);
            $userActionService = $container->make(UserActionService::class);
            if(!empty($cpType) &&  $cpType != $userActionService->getCpType()){
                continue;
            }

            if(!empty($actionType) &&  $actionType != $userActionService->getType()){
                continue;
            }

            if(!empty($productType) &&  $productType != $userActionService->getProductType()){
                continue;
            }

            !empty($productId) && $userActionService->setParam('product_ids',[$productId]);


            $tmpStartTime = $startTime;
            while($tmpStartTime < $endTime){
                $tmpEndTime = date('Y-m-d H:i:s',  strtotime($tmpStartTime) + $timeInterval);

                echo "时间 : {$tmpStartTime} ~ {$tmpEndTime}";

                $userActionService->setParam('start_time',$tmpStartTime);
                $userActionService->setParam('end_time',$tmpEndTime);
                $userActionService->checkDiffWithSync();
                $tmpStartTime = $tmpEndTime;
            }
        }

        $lockKey = "check|{$cpType}|{$productType}|{$actionType}";



        $this->lockRun(function () use ($cpType,$productType,$actionType){

        },$lockKey,60*60*3,['log' => true]);


    }
}
