<?php

namespace App\Sdks\Bm\Traits;


trait User
{

    public function getUsers($startTime,$endTime,$page = 1){
        $uri = 'foreign/users';
        $param = [
            'regTimeStart' => $startTime,
            'regTimeEnd' => $endTime,
            'page'  => $page
        ];

        return $this->apiRequest($uri,$param);
    }

}
