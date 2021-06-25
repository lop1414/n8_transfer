<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Enums\UserActionTypeEnum;
use App\Services\ProductService;
use App\Services\PushAllUserRegActionService;
use App\Services\PushUserActionService;

class PushAllUserRegActionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push_all_user_reg_action';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '推送用户注册行为数据';

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


        $lockKey = "push_all_user_reg_action";


        $this->lockRun(function (){

            (new PushAllUserRegActionService())->push();
        },$lockKey,60*60*3,['log' => true]);

    }










}
