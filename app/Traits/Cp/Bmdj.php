<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\OrderTypeEnums;
use App\Common\Sdks\Bmdj\BmdjSdk;


trait Bmdj
{

    public function getCpType(): string
    {
        return CpTypeEnums::BMDJ;
    }


    protected function getSdk(array $product): BmdjSdk
    {
        return new BmdjSdk($product['cp_account']['account'],$product['cp_account']['cp_secret'],$product['cp_product_alias']);

    }

    protected function getOrderType($orderType){
        $orderTypeMap = [
            1   => OrderTypeEnums::PROP,
            2   => OrderTypeEnums::NORMAL,
            3   => OrderTypeEnums::NORMAL,
            4   => OrderTypeEnums::ACTIVITY,
            5   => OrderTypeEnums::OTHER,
            6   => OrderTypeEnums::OTHER,
        ];
        return $orderTypeMap[$orderType];
    }

}
