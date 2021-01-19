<?php

namespace App\Sdks\Yw\Traits;


trait Product
{

    public function getProduct($param){
        $uri = 'cpapi/wxRecharge/getapplist';

        return $this->apiRequest($uri,$param);
    }

}
