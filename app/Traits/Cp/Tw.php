<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\OrderTypeEnums;
use App\Common\Sdks\Tw\TwSdk;

trait Tw
{

    public function getCpType(): string
    {
        return CpTypeEnums::TW;
    }


    protected function getSdk(array $product): TwSdk
    {
        return new TwSdk($product['cp_product_alias'],$product['cp_account']['cp_secret']);
    }

    protected function getOrderType($orderType){
        $orderTypeMap = [
            1   => OrderTypeEnums::NORMAL,
            2   => OrderTypeEnums::PROP,
            3   => OrderTypeEnums::ACTIVITY,
            4   => OrderTypeEnums::NORMAL,
        ];
        return $orderTypeMap[$orderType];
    }

}
