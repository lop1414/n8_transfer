<?php

namespace App\Sdks\Qy\Traits;


trait User
{



    public function getUsers($date,$page = 1, $pageSize = 100){
        $uri = 'v1/users';
        $param = [
            'date'  => $date,
            'page'  => $page,
            'size'  => $pageSize
        ];

        return $this->apiRequest($uri,$param);
    }

}
