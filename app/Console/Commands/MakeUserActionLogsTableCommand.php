<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;
use App\Services\MakeUserActionLogsTableService;

class MakeUserActionLogsTableCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'make:user_action_logs_table {--year=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '制作用户行为日志表';

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

        $year = $this->option('year') ?: date('Y');

        // 校验
        if(date('Y', strtotime($year."-01-01")) !== $year){
            throw new CustomException([
                'code' => 'YEAR_ERROR',
                'message' => '年份错误',
            ]);
        }


        $service = new MakeUserActionLogsTableService();

        $this->lockRun(function () use ($service,$year){

            $service->make($year);
        },'make_user_action_logs_table','60',['log' => true]);


    }


}
