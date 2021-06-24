<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\StatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Services\ProductService;
use App\Services\YwFillUserActionInfoService;

class FillUserActionInfoCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'fill_user_action_info {--time=} {--product_id=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '补充用户行为信息';

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


    /**
     * 目前就补充阅文快应用 后面需要再扩展
     * @throws \App\Common\Tools\CustomException
     */
    public function handle(){
        $this->lockRun(function (){
            $this->action();
        },'fill_user_action_info',60*30,['log' => true]);
    }

    public function action(){
        $time    = $this->option('time');
        $productId    = $this->option('product_id');
        list($startTime,$endTime) = Functions::getTimeRange($time);
        $endTime = min($endTime,date('Y-m-d H:i:s'));


        $productList = (new ProductService())->get([
            'cp_type' => CpTypeEnums::YW,
            'status'  => StatusEnum::ENABLE
        ]);
        foreach ($productList as $product){

            //指定产品id
            if(!empty($productId) && $productId != $product['id']){
                continue;
            }

            echo $product['name']."\n";
            (new YwFillUserActionInfoService())
                ->setProduct($product)
                ->cpChannelId($startTime,$endTime);
        }
    }


}
