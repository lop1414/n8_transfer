<?php

namespace App\Sdks\Tw\Traits;


trait Order
{

    public function getOrders($param){
        $uri = '/minute_order';

        return $this->apiRequest($uri,$param);
    }

}
