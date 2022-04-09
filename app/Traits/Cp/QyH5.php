<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\OrderTypeEnums;
use App\Common\Sdks\Qy\QySdk;

trait QyH5
{

    public function getCpType(): string
    {
        return CpTypeEnums::QY;
    }


    protected function getSdk(array $product): QySdk
    {
        return new QySdk($product['cp_secret']);
    }


    protected function getOrderType($orderType){
        $orderTypeMap = [
            1   => OrderTypeEnums::NORMAL,
            2   => OrderTypeEnums::PROP
        ];
        return $orderTypeMap[$orderType];
    }



}
