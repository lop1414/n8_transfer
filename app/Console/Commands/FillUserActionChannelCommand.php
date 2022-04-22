<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Helpers\Functions;
use App\Services\ProductService;
use App\Services\UserAction\UserActionInterface;
use App\Services\UserActionService;
use Illuminate\Container\Container;


class FillUserActionChannelCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'fill_user_action_channel {--time=} {--product_id=} {--cp_type=} {--product_type=} {--time_interval=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '补充用户行为渠道';


    /**
     * @var
     * 时间间隔
     */
    protected $timeInterval = 60*30;



    /**
     * @throws \App\Common\Tools\CustomException
     */
    public function handle(){

        $cpType = $this->option('cp_type');
        $cpType && Functions::hasEnum(CpTypeEnums::class, $cpType);


        $productType = $this->option('product_type');
        $productType && Functions::hasEnum(ProductTypeEnums::class, $productType);

        $lockKey = "fill_channel|{$cpType}|{$productType}";

        $this->lockRun(function () use ($cpType,$productType){

            $this->action($cpType,$productType);

        },$lockKey,60*60*3,['log' => true]);
    }



    public function action($cpType,$productType){
        // 时间参数
        $time = $this->option('time');
        list($startTime,$endTime) = explode(",", $time);
        $endTime = min($endTime,date('Y-m-d H:i:s'));
        Functions::checkTimeRange($startTime,$endTime);

        // 时间区间
        $timeInterval = $this->option('time_interval') ?? $this->timeInterval;
        $productId = $this->option('product_id');
        if(!empty($productId)){
            $productService = new ProductService();
            $products = $productService->get(['id' => $productId]);
            $product = $products[0];
            $cpType = $product['cp_type'];
            $productType = $product['type'];
        }

        $container = Container::getInstance();
        $services = UserActionService::getNeedFillChannelService();
        foreach ($services as $service){
            $container->bind(UserActionInterface::class,$service);
            $userActionService = $container->make(UserActionService::class);

            if(!empty($cpType) &&  $cpType != $userActionService->getCpType()){
                continue;
            }

            if(!empty($productType) &&  $productType != $userActionService->getProductType()){
                continue;
            }

            !empty($productId) && $userActionService->setParam('product_id',$productId);


            $tmpStartTime = $startTime;
            while($tmpStartTime < $endTime){
                $tmpEndTime = date('Y-m-d H:i:s',  strtotime($tmpStartTime) + $timeInterval);

                echo "时间 : {$tmpStartTime} ~ {$tmpEndTime}";

                $userActionService->setParam('start_time',$tmpStartTime);
                $userActionService->setParam('end_time',$tmpEndTime);
                $userActionService->fillUserChannel();
                $tmpStartTime = $tmpEndTime;
            }
        }
    }
}
