<?php

namespace App\Sdks\TwApp\Traits;


trait Order
{

    public function getOrders($param = []){
        $uri = 'dataapi/getorder';

        return $this->apiRequest($uri,$param);
    }

}
