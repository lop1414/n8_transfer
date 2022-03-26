<?php

namespace App\Sdks\TwApp\Traits;


trait CpChannel
{

    public function getCpChannel($param = []){
        $uri = 'dataapi/getchannel';

        return $this->apiRequest($uri,$param);
    }

}
