<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Services\CreateTableService;

class CreateTableCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'create_table {--date=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '创建表';

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


        $tmp = explode('》',"《九域剑帝》第五章 灵药殿前");
        dd(trim($tmp[0],'《'));
        $service = new CreateTableService();

        $date    = $this->option('date');
        if(!empty($date)){
            Functions::dateCheck($date);
            $suffix = date('Ym',strtotime($date));
        }else{
            $suffix = date('Ym',strtotime('+1 month'));

        }

        $service->setSuffix($suffix);
        $service->create();
    }


}
