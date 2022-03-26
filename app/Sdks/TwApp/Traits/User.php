<?php

namespace App\Sdks\TwApp\Traits;


trait User
{

    public function getUsers($param = []){
        $uri = 'dataapi/getuser';

        return $this->apiRequest($uri,$param);
    }

}
