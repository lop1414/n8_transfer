<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\OrderTypeEnums;
use App\Common\Sdks\Bm\BmSdk;

trait Bm
{

    public function getCpType(): string
    {
        return CpTypeEnums::BM;
    }


    protected function getSdk(array $product): BmSdk
    {
        return  new BmSdk($product['cp_product_alias'],$product['cp_secret']);
    }


    protected function getOrderType($orderType){
        $orderTypeMap = [
            1   => OrderTypeEnums::ANNUAL,
            2   => OrderTypeEnums::NORMAL,
            3   => OrderTypeEnums::NORMAL,
            4   => OrderTypeEnums::ACTIVITY
        ];
        return $orderTypeMap[$orderType];
    }



}
