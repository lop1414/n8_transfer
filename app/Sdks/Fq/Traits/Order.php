<?php

namespace App\Sdks\Fq\Traits;


trait Order
{

    public function getOrders($startTime,$endTime,$offset = 0, $limit = 1000){
        $uri = 'novelsale/openapi/user/recharge/v1';
        $param = [
            'begin' => strtotime($startTime),
            'end' => strtotime($endTime),
            'limit'  => $limit,
            'offset' => $offset
        ];
        return $this->apiRequest($uri,$param);
    }

}
