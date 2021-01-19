<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;
use App\Services\AnalogPushService;
use App\Services\SyncProductService;

class SyncProductCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'sync_product';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步产品信息';

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


        $service = new SyncProductService();


        $this->lockRun(function () use ($service){

            $service->sync();

        },'sync_product','60',['log' => true]);


    }


}
