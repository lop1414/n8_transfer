<?php

namespace App\Console\Commands\Yw;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Services\ProductService;

class CheckOrderCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'yw:check_order {--time=} {--time_interval=} {--product_id=}';


    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '阅文快应用：检测订单';


    protected $consoleEchoService;



    /**
     * @var
     * 时间间隔
     */
    protected $timeInterval = 60*60;

    /**
     * @var
     * 时间区间
     */
    protected $startTime,$endTime;


    protected $productId;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
        $this->consoleEchoService = new ConsoleEchoService();
    }



    public function handle(){


        $this->productId = $this->option('product_id');
        $time = $this->option('time');
        $timeInterval = $this->option('time_interval');


        list($this->startTime,$this->endTime) = explode(",", $time);
        $this->endTime = min($this->endTime,date('Y-m-d H:i:s'));
        Functions::checkTimeRange($this->startTime,$this->endTime);



        // 设置值
        if(!empty($timeInterval)){
            $this->timeInterval = $timeInterval;
        }

        $lockKey = 'yw_kyy|check_order';

        $this->lockRun(function (){

            $this->action();
        },$lockKey,60*60*3,['log' => true]);


    }


    public function action(){
        $productList = (new ProductService())->get([
            'cp_type' => CpTypeEnums::YW,
            'status'  => StatusEnum::ENABLE
        ]);

        $tmpService = null;
        foreach ($productList as $product){
            //指定产品id
            if(!empty($this->productId) && $this->productId != $product['id']){
                continue;
            }

            $this->consoleEchoService->echo("产品 : {$product['name']}\n\n\n");

            if($product['type'] == ProductTypeEnums::KYY){
                $tmpService = (new \App\Services\YwKyy\UserOrderActionService())->setProduct($product);
            }elseif($product['type'] == ProductTypeEnums::H5){
                $tmpService = (new \App\Services\YwH5\UserOrderActionService())->setProduct($product);
            }

            $time = $this->startTime;
            while($time < $this->endTime){
                $tmpEndTime = date('Y-m-d H:i:s',  strtotime($time) + $this->timeInterval);

                $this->consoleEchoService->echo("时间 : {$time} ~ {$tmpEndTime}");

                $tmpService->setTimeRange($time, $tmpEndTime);
                $tmpService->check();

                $time = $tmpEndTime;
            }


        }
    }

}
