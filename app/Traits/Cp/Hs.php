<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\OrderTypeEnums;
use App\Common\Sdks\Hs\HsSdk;

trait Hs
{

    public function getCpType(): string
    {
        return CpTypeEnums::HS;
    }


    protected function getSdk(array $product): HsSdk
    {
        $cpAccount =  $product['cp_account'];
        list($apiKey,$apiSecurity) = explode('#',$cpAccount['cp_secret']);
        return new HsSdk($cpAccount['account'],$apiKey,$apiSecurity);
    }


    protected function getOrderType($orderType){
        $orderTypeMap = [
            0   => OrderTypeEnums::NORMAL,
            1   => OrderTypeEnums::PROP,
            2   => OrderTypeEnums::PROP,
        ];
        return $orderTypeMap[$orderType];
    }



}
