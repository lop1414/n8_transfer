<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\AdvAliasEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;
use App\Services\AdvClick\BaiDuClickService;
use App\Services\AdvClick\OceanClickService;

class PushAdvClickCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push_adv_click {--adv_alias=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '推送点击数据';


    protected $consoleEchoService;

    /**
     * @var
     * 时间区间
     */
    protected $startTime,$endTime;

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

        $advAlias  = $this->option('adv_alias');
        Functions::hasEnum(AdvAliasEnum::class,$advAlias);
        $action = Functions::camelize($advAlias);
        if(!method_exists($this,$action)){
            throw new CustomException([
                'code' => 'NOT_REALIZED',
                'message' => '未实现',
            ]);
        }


         $this->lockRun([$this,$action],'push_adv_click:'.$advAlias,60*60,['log' => true]);
    }


    public function ocean(){
        (new OceanClickService())->push();
    }


    public function bd(){
        (new BaiDuClickService())->push();
    }


}
