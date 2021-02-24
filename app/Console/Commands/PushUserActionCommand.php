<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;

class PushUserActionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push:user_action {--cp_type=} {--product_type=} {--time=} {--time_interval=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '上报用户行为数据';

    protected $consoleEchoService;

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

        $cpType = $this->option('cp_type');
        $productType = $this->option('product_type');
        $time = $this->option('time');
        $timeInterval = $this->option('time_interval') ?: 60;

        list($startTime,$endTime) = explode(",", $time);

        // 校验
        if(
            !isset($startTime) || !Functions::timeCheck($startTime) ||
            !isset($endTime) || !Functions::timeCheck($endTime) ||
            $startTime > $endTime
        ){
            throw new CustomException([
                'code' => 'DATE_RANGE_ERROR',
                'message' => '时间范围错误',
            ]);
        }

        $date = date_diff(date_create($startTime),date_create($endTime));
        if($date->y > 0 || $date->m){
            throw new CustomException([
                'code' => 'DATE_RANGE_ERROR',
                'message' => '时间范围不能跨月/年',
            ]);
        }

        if(!isset($cpType)){
            throw new CustomException([
                'code' => 'UNVALID',
                'message' => 'cp_type 必传',
            ]);
        }

        if(!isset($productType)){
            throw new CustomException([
                'code' => 'UNVALID',
                'message' => 'product_type 必传',
            ]);
        }


        Functions::hasEnum(CpTypeEnums::class, $cpType);
        Functions::hasEnum(ProductTypeEnums::class, $productType);


        $class = "App\Services\\{$cpType}\\Push{$productType}UserActionService";

        if(!class_exists($class)){
            throw new CustomException([
                'code' => 'NOT_REALIZED',
                'message' => '未实现',
            ]);
        }

        $service = new $class();
        $service->setTimeInterval($timeInterval);
        $service->setTimeRange($startTime,$endTime);


        $lockKey = "{$cpType}:{$productType}:pull_user_action_log";
        $this->lockRun(function () use ($service){

            $service->reg();
            $service->bind_channel();
            $service->addShortcut();
            $service->order();
            $service->complete_order();

        },$lockKey,60*10,['log' => true]);


    }


}
