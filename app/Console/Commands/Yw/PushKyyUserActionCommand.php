<?php

namespace App\Console\Commands\Yw;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;
use App\Services\Yw\PullKyyUserActionService;
use App\Services\Yw\PushKyyUserActionService;

class PushKyyUserActionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push:kyy:user_action {--time=}';

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


        $service = new PushKyyUserActionService();
        $service->setTimeRange($startTime,$endTime);


        $this->lockRun(function () use ($service){

//            $service->reg();
//            $service->bind_channel();
//            $service->addShortcut();
//            $service->order();
            $service->complete_order();

        },'push_kyy_user_action_log',60*5,['log' => true]);


    }


}
