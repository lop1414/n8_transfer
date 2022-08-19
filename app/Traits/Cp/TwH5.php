<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\OrderTypeEnums;
use App\Common\Sdks\TwH5\TwH5Sdk;

trait TwH5
{

    public function getCpType(): string
    {
        return CpTypeEnums::TW;
    }


    protected function getSdk(array $product): TwH5Sdk
    {
        return new TwH5Sdk($product['cp_product_alias'],$product['cp_secret']);
    }


    protected function getOrderType($type){
        $orderTypeMap = [
            1   => OrderTypeEnums::NORMAL,
            2   => OrderTypeEnums::PROP,
            3   => OrderTypeEnums::ACTIVITY,
            4   => OrderTypeEnums::NORMAL,
            7   => OrderTypeEnums::NORMAL,
            8   => OrderTypeEnums::NORMAL,
        ];
        return $orderTypeMap[$type];
    }



}
