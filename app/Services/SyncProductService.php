<?php

namespace App\Services;

use App\Common\Enums\StatusEnum;
use App\Common\Services\BaseService;
use App\Common\Services\ConsoleEchoService;
use App\Enums\CpTypeEnums;
use App\Enums\ProductTypeEnums;
use App\Models\CpAccountModel;
use App\Models\ProductModel;
use App\Sdks\Yw\YwSdk;


class SyncProductService extends BaseService
{


    public $echoService;




    public function __construct(){
        $this->echoService = new ConsoleEchoService();
    }


    public function sync(){
        $this->yw();
    }


    public function yw(){
        $model = new CpAccountModel();
        $list = $model->where('cp_type',CpTypeEnums::YW)->get();

        $productModel = new ProductModel();
        foreach ($list as $item){
            $sdk = new YwSdk('',$item->account,$item->cp_secret);
            $data = $sdk->getProduct([
                'coop_type'   => 11,
                'start_time'  => strtotime('2020-06-01'),
                'end_time'    => time()
            ]);

            foreach ($data['list'] as $product){
                $pro = $productModel->where('cp_product_alias',$product['appflag'])
                    ->where('type',ProductTypeEnums::KYY)
                    ->where('cp_type',CpTypeEnums::YW)
                    ->first();
                if(empty($pro)){
                    $pro = new ProductModel();
                    $pro->cp_product_alias = $product['appflag'];
                    $pro->cp_type = CpTypeEnums::YW;
                    $pro->status = StatusEnum::ENABLE;
                    $pro->account_id = $item->id;
                    $pro->type = ProductTypeEnums::KYY;
                    $pro->secret = '';
                }

                $pro->name = $product['app_name'];
                $pro->save();
            }
        }
    }


}
