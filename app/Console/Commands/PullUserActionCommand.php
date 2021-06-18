<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;
use App\Enums\UserActionTypeEnum;
use App\Services\ProductService;

class PullUserActionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'pull_user_action {--cp_type=} {--product_type=} {--action_type=}  {--time=} {--time_interval=} {--second_version=} {--product_id=}';


    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '拉取用户行为数据';


    protected $consoleEchoService;

    /**
     * @var
     * 书城类型
     */
    protected $cpType;

    /**
     * @var
     * 产品类型
     */
    protected $productType;

    /**
     * @var
     * 行为类型
     */
    protected $actionType;


    /**
     * @var
     * 时间间隔
     */
    protected $timeInterval = 60*30;

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

        $this->cpType = $this->option('cp_type');
        $this->productType = $this->option('product_type');
        $this->actionType = $this->option('action_type');
        $this->productId = $this->option('product_id');
        $time = $this->option('time');
        $isSecondVersion = $this->option('second_version');
        $timeInterval = $this->option('time_interval');




        Functions::hasEnum(CpTypeEnums::class, $this->cpType);
        Functions::hasEnum(ProductTypeEnums::class, $this->productType);
        Functions::hasEnum(UserActionTypeEnum::class, $this->actionType);

        list($this->startTime,$this->endTime) = explode(",", $time);
        $this->endTime = min($this->endTime,date('Y-m-d H:i:s'));
        Functions::checkTimeRange($this->startTime,$this->endTime);



        // 设置值
        if(!empty($timeInterval)){
            $this->timeInterval = $timeInterval;
        }

        $lockKey = "pull|{$this->cpType}|{$this->productType}|{$this->actionType}";
        if($isSecondVersion){
            $lockKey = "pull|{$this->cpType}|{$this->productType}|{$this->actionType}|SecondVersion";
        }

        $this->lockRun(function (){

            $this->action();
        },$lockKey,60*60*3,['log' => true]);


    }


    public function action(){
        $service = $this->getService();
        $productList = (new ProductService())->get([
            'cp_type' => $this->cpType,
            'type'    => $this->productType
        ]);

        foreach ($productList as $product){
            //指定产品id
            if(!empty($this->productId) && $this->productId != $product['id']){
                continue;
            }

            $this->consoleEchoService->echo("产品 : {$product['name']}\n\n\n");

            $service->setProduct($product);

            $time = $this->startTime;
            while($time < $this->endTime){
                $tmpEndTime = date('Y-m-d H:i:s',  strtotime($time) + $this->timeInterval);

                $this->consoleEchoService->echo("时间 : {$time} ~ {$tmpEndTime}");

                $service->setTimeRange($time, $tmpEndTime);
                $service->pull();

                $time = $tmpEndTime;
            }


        }
    }


    public function getService(){
        $cpType = ucfirst(Functions::camelize($this->cpType));
        $productType = ucfirst(Functions::camelize($this->productType));
        $actionType = ucfirst(Functions::camelize($this->actionType));

        $isSecondVersion = $this->option('second_version');
        $tmp = $isSecondVersion ? 'SecondVersion' :'';

        $class = "App\Services\\{$tmp}{$cpType}{$productType}\\User{$actionType}ActionService";

        if(!class_exists($class)){
            throw new CustomException([
                'code' => 'NOT_REALIZED',
                'message' => '未实现',
            ]);
        }
        return new $class();
    }

}
