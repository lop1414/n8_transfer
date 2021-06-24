<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Common\Tools\CustomException;

class PushPageChannelCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push_page_channel {--cp_type=} {--product_type=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步页面渠道到联运系统';

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

        $this->cpType    = $this->option('cp_type');
        $this->productType   = $this->option('product_type');
        Functions::hasEnum(CpTypeEnums::class, $this->cpType);
        Functions::hasEnum(ProductTypeEnums::class, $this->productType);


        $service = $this->getService();
        $service->run();

    }





    public function getService(){
        $cpType = ucfirst(Functions::camelize($this->cpType));
        $productType = ucfirst(Functions::camelize($this->productType));


        $class = "App\Services\\{$cpType}{$productType}\\PushPageChannelService";

        if(!class_exists($class)){
            throw new CustomException([
                'code' => 'NOT_REALIZED',
                'message' => '未实现',
            ]);
        }
        return new $class();
    }




}
