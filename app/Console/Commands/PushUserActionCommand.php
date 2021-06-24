<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Enums\UserActionTypeEnum;
use App\Services\ProductService;
use App\Services\PushUserActionService;

class PushUserActionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push_user_action {--cp_type=} {--product_type=} {--action_type=} {--is_all=} {--time=} {--time_interval=} {--product_id=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '推送用户行为数据';

    protected $consoleEchoService;

    /**
     * @var
     * 行为类型
     */
    protected $actionType;

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
     * 产品id
     */
    protected $productId;

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

    /**
     * @var
     * 推送所有
     */
    protected $isAll;

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
        $time = $this->option('time');
        $this->isAll = $this->option('is_all');
        $this->productId = $this->option('product_id');
        $timeInterval = $this->option('time_interval');

        Functions::hasEnum(CpTypeEnums::class, $this->cpType);
        Functions::hasEnum(ProductTypeEnums::class, $this->productType);
        Functions::hasEnum(UserActionTypeEnum::class, $this->actionType);

        if($this->isAll != 1){
            list($this->startTime,$this->endTime) = explode(",", $time);
            $this->endTime = min($this->endTime,date('Y-m-d H:i:s'));
            Functions::checkTimeRange($this->startTime,$this->endTime);
        }


        // 设置值
        if(!empty($timeInterval)){
            $this->timeInterval = $timeInterval;
        }


        $lockKey = "push|{$this->cpType}|{$this->productType}|{$this->actionType}";


        $this->lockRun(function (){

            $this->action();
        },$lockKey,60*60*3,['log' => true]);

    }


    public function action(){
        $service = (new PushUserActionService())->setActionType($this->actionType);


        $productList = (new ProductService())->get([
            'cp_type' => $this->cpType,
            'type'    => $this->productType,
            'status'  => StatusEnum::ENABLE
        ]);

        foreach ($productList as $product){
            //指定产品id
            if(!empty($this->productId) && $this->productId != $product['id']){
                continue;
            }

            $this->consoleEchoService->echo("产品 : {$product['name']}\n\n\n");

            $service->setProduct($product);

            if($this->isAll == 1){
                $service->pushAll();
            }else{
                $time = $this->startTime;
                while($time < $this->endTime){
                    $tmpEndTime = date('Y-m-d H:i:s',  strtotime($time) + $this->timeInterval);

                    $this->consoleEchoService->echo("时间 : {$time} ~ {$tmpEndTime}");

                    $service->setTimeRange($time, $tmpEndTime);
                    $service->push();

                    $time = $tmpEndTime;
                }
            }

        }
    }









}
