<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;
use App\Services\AnalogPushService;

class AnalogPushCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'analog_push {--time=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '推送用户行为数据';

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

        $time = $this->option('time');
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


        $service = new AnalogPushService();
        $service->setTimeRange($startTime,$endTime);


        // 调试模式不锁
        $expire = Functions::isDebug() ? 1 : 60 ;

        $this->lockRun(function () use ($service){

            $service->ywKyyUserAction();
            $service->ywKyyUserPay();

            $service->twKyyRegAction();
            $service->twKyyPayAction();
        },'analog_push',$expire,['log' => true]);


    }


}
