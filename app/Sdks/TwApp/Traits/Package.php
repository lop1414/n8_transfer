<?php

namespace App\Sdks\TwApp\Traits;


trait Package
{

    public function getPackage($param = []){
        $uri = 'dataapi/getpackage';

        return $this->apiRequest($uri,$param);
    }

}
