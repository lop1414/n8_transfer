<?php

namespace App\Sdks\Bm\Traits;


trait Order
{

    public function getOrders($startTime,$endTime,$page = 1){
        $uri = 'foreign/orders';
        $param = [
            'createTimeStart' => $startTime,
            'createTimeEnd' => $endTime,
            'page'  => $page
        ];
        return $this->apiRequest($uri,$param);
    }

}
