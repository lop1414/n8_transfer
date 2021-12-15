<?php

namespace App\Sdks\Fq\Traits;


trait AddDesktop
{

    public function getAddDesktopActions($startTime,$endTime,$offset = 0, $limit = 1000){
        $uri = 'novelsale/openapi/user/add_desktop/v1';
        $param = [
            'begin' => strtotime($startTime),
            'end' => strtotime($endTime),
            'limit'  => $limit,
            'offset' => $offset
        ];

        return $this->apiRequest($uri,$param);
    }



}
