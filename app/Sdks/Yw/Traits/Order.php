<?php

namespace App\Sdks\Yw\Traits;


use App\Enums\ProductTypeEnums;

trait Order
{

    public function getOrders($startTime,$endTime){
        $uri = 'cpapi/wxRecharge/quickappchargelog';
        $param = [
            'start_time'  => $startTime,
            'end_time'    => $endTime,
        ];

        if($this->product_type == ProductTypeEnums::KYY){
            $param['coop_type'] = 11;
        }

        return $this->apiRequest($uri,$param);
    }

}
