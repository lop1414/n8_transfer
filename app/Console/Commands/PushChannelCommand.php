<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;

class PushChannelCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push_channel {--date=} {--cp_type=} {--product_type=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步渠道到联运系统';

    protected $consoleEchoService;


    /**
     * @var
     * 书城类型
     */
    protected $cpType;

    /**
     * @var
     * 产品类型
     */
    protected $productType;


    /**
     * @var
     * 时间区间
     */
    protected $startDate,$endDate;


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
        $tmpDate    = $this->option('date');

        list($this->startDate,$this->endDate) = explode(",", $tmpDate);
        Functions::checkDateRange($this->startDate,$this->endDate);

        $this->cpType    = $this->option('cp_type');
        $this->productType   = $this->option('product_type');
        Functions::hasEnum(CpTypeEnums::class, $this->cpType);
        Functions::hasEnum(ProductTypeEnums::class, $this->productType);


        $service = $this->getService();
        $date = $this->startDate;
        while($date <= $this->endDate){
            $tmpEndDate = date('Y-m-d',  strtotime('+1 day',strtotime($date)));

            $this->consoleEchoService->echo("时间 : {$date} ~ {$tmpEndDate}");

            $service->setDateRange($date,$tmpEndDate)->run();

            $date = $tmpEndDate;
        }

    }





    public function getService(){
        $cpType = ucfirst(Functions::camelize($this->cpType));
        $productType = ucfirst(Functions::camelize($this->productType));


        $class = "App\Services\\{$cpType}{$productType}\\PushChannelService";

        if(!class_exists($class)){
            throw new CustomException([
                'code' => 'NOT_REALIZED',
                'message' => '未实现',
            ]);
        }
        return new $class();
    }




}
