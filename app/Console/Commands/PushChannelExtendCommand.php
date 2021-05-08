<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Services\ChannelService;

class PushChannelExtendCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push_channel_extend {--cp_type=} {--time=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '推送渠道扩展信息';


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
        Functions::checkTimeRange($startTime,$endTime);

        $cpType  = $this->option('cp_type');
        if(!empty($cpType)){
            Functions::hasEnum(CpTypeEnums::class,$cpType);
        }


        (new ChannelService())->pushChannelExtend($startTime,$endTime,$cpType);
    }





}
