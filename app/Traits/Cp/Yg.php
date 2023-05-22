<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\OrderTypeEnums;
use App\Common\Sdks\Yg\YgSdk;


trait Yg
{

    public function getCpType(): string
    {
        return CpTypeEnums::YG;
    }


    protected function getSdk(array $product): YgSdk
    {
        return new YgSdk($product['cp_account']['account'],$product['cp_account']['cp_secret']);

    }

    public function getOrderType($orderType){
        $orderTypeMap = [
            1   => OrderTypeEnums::NORMAL,
            2   => OrderTypeEnums::OTHER,
        ];
        return $orderTypeMap[$orderType];
    }

}
