<?php

namespace App\Http\Controllers;

use App\Common\Controllers\Front\FrontController;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Enums\StatusEnum;
use App\Services\BmKyy\UserRegActionService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class TestController extends FrontController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }



    public function test(Request $request){
        $key = $request->input('key');
        if($key != 'aut'){
            return $this->forbidden();
        }

        $this->demo();
        $productList = (new ProductService())->get([
            'cp_type' => CpTypeEnums::BM,
            'type'    => ProductTypeEnums::KYY,
            'status'  => StatusEnum::ENABLE
        ]);
        $service = (new UserRegActionService())->setProduct($productList[0]);
        $service->setTimeRange('2021-05-25 16:00:00', '2021-05-25 17:00:00');
//        $service->setTimeRange('2021-08-26 00:00:00', '2021-08-26 17:00:00');
        $service->pull();


    }


    public function demo(){
        $productList = (new ProductService())->get([
            'cp_type' => CpTypeEnums::YW,
            'type'    => ProductTypeEnums::H5,
            'status'  => StatusEnum::ENABLE,
            'id'      => 109
        ]);
        $service = (new \App\Services\YwH5\UserRegActionService())->setProduct($productList[0]);
        $service->pull();
    }


}
