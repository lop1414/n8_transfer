<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\OrderTypeEnums;
use App\Common\Sdks\Qr\QrSdk;


trait Qr
{

    public function getCpType(): string
    {
        return CpTypeEnums::QR;
    }


    protected function getSdk(array $product): QrSdk
    {
        return new QrSdk($product['extends']['host_id'],$product['cp_product_alias'],$product['cp_secret']);

    }

    protected function getOrderType($orderType){
        $orderTypeMap = [
            0   => OrderTypeEnums::NORMAL,
            1   => OrderTypeEnums::OTHER,
            2   => OrderTypeEnums::OTHER,
            3   => OrderTypeEnums::OTHER,
            4   => OrderTypeEnums::OTHER,
            5   => OrderTypeEnums::OTHER,
            6   => OrderTypeEnums::OTHER,
            7   => OrderTypeEnums::OTHER,
        ];
        return $orderTypeMap[$orderType];
    }

}
