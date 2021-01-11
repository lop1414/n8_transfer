<?php

namespace App\Sdks\Yw\Traits;


trait Order
{

    public function getOrders($param){
        $uri = 'cpapi/wxRecharge/quickappchargelog';

        return $this->apiRequest($uri,$param);
    }

}
