<?php

namespace App\Sdks\Qy\Traits;


trait Order
{



    public function getOrders($date,$page = 1, $pageSize = 50){
        $uri = 'v1/orders';
        $param = [
            'date'  => $date,
            'page'  => $page,
            'size'  => $pageSize
        ];

        return $this->apiRequest($uri,$param);
    }

}
