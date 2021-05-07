<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\AdvAliasEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;
use App\Services\AdvClick\OceanClickService;
use App\Services\CreateTableService;

class PushAdvClickCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push_adv_click {--adv_alias=} {--time=}';

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
        $time = $this->option('time');
        list($startTime,$endTime) = explode(",", $time);
        Functions::checkTimeRange($startTime,$endTime);

        $advAlias  = $this->option('adv_alias');
        Functions::hasEnum(AdvAliasEnum::class,$advAlias);
        $action = ucfirst(Functions::camelize($advAlias));

        if(!method_exists($this,$action)){
            throw new CustomException([
                'code' => 'NOT_REALIZED',
                'message' => '未实现',
            ]);
        }

        $this->startTime = $startTime;
        $this->endTime = $endTime;

        $this->$action();
    }


    public function ocean(){
        (new OceanClickService())->setTimeRange($this->startTime,$this->endTime)->push();
    }


}
