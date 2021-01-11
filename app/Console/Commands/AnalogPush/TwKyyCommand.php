<?php

namespace App\Console\Commands\AnalogPush;

use App\Common\Console\BaseCommand;
use App\Common\Services\ConsoleEchoService;
use App\Services\AnalogPushService;

class TwKyyCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'analog_push:tw_kyy';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '推送腾文快应用用户行为数据';

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

        $statDate = "2021-01-01 00:00:00";
        $endDate = "2021-01-01 00:10:00";
        $service = new AnalogPushService();
        $service->setTimeRange($statDate,$endDate);
        $service->twKyyRegAction();
    }


}
