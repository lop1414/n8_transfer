<?php

namespace App\Console\Commands\AnalogPush\YwKyy;

use App\Common\Console\BaseCommand;
use App\Common\Services\ConsoleEchoService;
use App\Services\AnalogPushService;

class UserActionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'analog_push:yw_kyy_user_action';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '推送阅文快应用用户行为数据';

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

        $statDate = "2021-01-07 01:00:00";
        $endDate = "2021-01-07 02:00:00";
        $service = new AnalogPushService();
        $service->setTimeRange($statDate,$endDate);
        $service->ywKyyUserAction();
    }


}
