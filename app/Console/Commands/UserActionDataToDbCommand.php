<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Enums\QueueEnums;
use App\Services\UserActionDataToDbService;

class UserActionDataToDbCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'user_action_data_to_db {--enum=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '队列行为数据入库';

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
        $this->enum    = $this->option('enum');
        if(is_null($this->enum)){
            $this->consoleEchoService->error('enum 参数必传');
            return ;
        }

        Functions::hasEnum(QueueEnums::class,$this->enum);



        $key = 'user_action_data_to_db|'.$this->enum;
        $this->lockRun(function (){
            (new UserActionDataToDbService())
                ->setQueueEnum($this->enum)
                ->run();
        },$key,60*60,['log' => true]);
    }


}
