<?php

namespace App\Console\Commands\AnalogPush;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;
use App\Services\AnalogPushService;

class TwKyyCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'analog_push:tw_kyy {--time=}';

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
        $time = $this->option('time');
        list($statTime,$endTime) = explode(",", $time);

        // 校验
        if(
            !isset($statTime) || !Functions::timeCheck($statTime) ||
            !isset($endTime) || !Functions::timeCheck($endTime) ||
            $statTime > $endTime
        ){
            throw new CustomException([
                'code' => 'DATE_RANGE_ERROR',
                'message' => '时间范围错误',
            ]);
        }

        $service = new AnalogPushService();
        $service->setTimeRange($statTime,$endTime);
        $service->twKyyRegAction();
        $service->twKyyPayAction();
    }


}
