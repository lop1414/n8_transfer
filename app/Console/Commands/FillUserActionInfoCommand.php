<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Helpers\Functions;
use App\Common\Services\ConsoleEchoService;
use App\Services\ProductService;
use App\Services\YwKyy\FillUserActionInfoService;

class FillUserActionInfoCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'fill_user_action_info {--time=} {--product_id=} {--type=}';

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
        $time    = $this->option('time');
        $productId    = $this->option('product_id');
        $type = $this->option('type');
        list($startTime,$endTime) = Functions::getTimeRange($time);

        $productList = (new ProductService())->get([
            'cp_type' => CpTypeEnums::YW,
            'type'    => ProductTypeEnums::KYY
        ]);
        foreach ($productList as $product){
            //指定产品id
            if(!empty($this->productId) && $productId != $product['id']){
                continue;
            }

            echo $product['name']."\n";
            $this->$type($product,$startTime,$endTime);
        }
    }


    public function channel($product,$startTime,$endTime){
        (new FillUserActionInfoService())
            ->setProduct($product)
            ->cpChannelId($startTime,$endTime);
    }


}
