<?php

namespace App\Traits\Cp;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\OrderTypeEnums;
use App\Common\Sdks\Qr\QrSdk;
use App\Common\Services\SystemApi\UnionApiService;


trait Qr
{

    public function getCpType(): string
    {
        return CpTypeEnums::QR;
    }


    protected function getSdk(array $product): QrSdk
    {
        $cpAccount = $product['cp_account'];
        list($hostId,$appid) = explode('#',$cpAccount['account']);
        return new QrSdk($hostId,$appid,$cpAccount['cp_secret']);
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
            8   => OrderTypeEnums::OTHER,
            9   => OrderTypeEnums::OTHER,
            10   => OrderTypeEnums::OTHER,
            11   => OrderTypeEnums::OTHER,
        ];
        return $orderTypeMap[$orderType];
    }


    /**
     * 获取青榕产品Map
     * @return array
     * @throws \App\Common\Tools\CustomException
     */
    protected function getQrProductMap(): array
    {
        $productList = (new UnionApiService())->apiGetProduct([
            'cp_type'=> CpTypeEnums::QR,
        ]);
        return array_column($productList,null,'cp_product_alias');
    }

}
