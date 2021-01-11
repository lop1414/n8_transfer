<?php

namespace App\Sdks\Tw\Traits;


trait User
{

    public function getUsers($param){
        $uri = '/minute_user';

        return $this->apiRequest($uri,$param);
    }

}
