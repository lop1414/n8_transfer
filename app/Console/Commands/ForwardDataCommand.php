<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Services\ConsoleEchoService;
use App\Services\ForwardDataService;

class ForwardDataCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'forward_data';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '转发数据';

    protected $consoleEchoService;

    /**
     * @var
     * 队列枚举
     */
    protected $enum;


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

        $this->lockRun(function (){
            (new ForwardDataService())->forward();
        },'forward_data',60*30,['log' => true]);
    }


}
