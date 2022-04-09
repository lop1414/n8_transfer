<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\OrderTypeEnums;
use App\Common\Sdks\Yw\YwSdk;

trait Yw
{

    public function getCpType(): string
    {
        return CpTypeEnums::YW;
    }


    protected function getSdk(array $product): YwSdk
    {
        return new YwSdk($product['cp_product_alias'],$product['cp_account']['account'],$product['cp_account']['cp_secret']);
    }


    protected function getOrderType($orderType){
        $orderTypeMap = [
            1   => OrderTypeEnums::NORMAL,
            2   => OrderTypeEnums::ANNUAL,
            3   => OrderTypeEnums::PROP
        ];
        return $orderTypeMap[$orderType];
    }
}
