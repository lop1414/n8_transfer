<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Enums\UserActionTypeEnum;
use App\Services\PushUserActionService;

class PushUserActionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push_user_action {--action_type=} {--product_id=} {--is_all=} {--time=} {--time_interval=} ';

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
        $this->productId = $this->option('product_id');
        $this->isAll = $this->option('is_all');
        $this->actionType = $this->option('action_type');
        if(!empty($this->actionType)){
            Functions::hasEnum(UserActionTypeEnum::class, $this->actionType);
        }


        if($this->isAll != 1){
            $time = $this->option('time');
            list($this->startTime,$this->endTime) = explode(",", $time);
            $this->endTime = min($this->endTime,date('Y-m-d H:i:s'));
            Functions::checkTimeRange($this->startTime,$this->endTime);

            // 设置值
            $timeInterval = $this->option('time_interval');
            if(!empty($timeInterval)){
                $this->timeInterval = $timeInterval;
            }
        }

        $this->lockRun(function (){

            $this->action();
        },"push|{$this->actionType}",60*60,['log' => true]);

    }


    public function action(){
        $service = new PushUserActionService();
        if(!empty($this->actionType)){
            $service->setActionType($this->actionType);
        }

        if(!empty($this->productId)){
            $service->setProduct($this->productId);
        }


        if($this->isAll == 1){
            $service->pushAll();
        }else{
            $time = $this->startTime;
            while($time < $this->endTime){
                $tmpEndTime = date('Y-m-d H:i:s',  strtotime($time) + $this->timeInterval);

                $this->consoleEchoService->echo("时间 : {$time} ~ {$tmpEndTime}");

                $service->push($time, $tmpEndTime);

                $time = $tmpEndTime;
            }
        }


    }









}
