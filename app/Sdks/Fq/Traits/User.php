<?php

namespace App\Sdks\Fq\Traits;


trait User
{

    public function getUsers($startTime,$endTime,$offset = 0, $limit = 1000){
        $uri = 'novelsale/openapi/user/distribution/v1';
        $param = [
            'begin' => strtotime($startTime),
            'end' => strtotime($endTime),
            'limit'  => $limit,
            'offset' => $offset
        ];

        return $this->apiRequest($uri,$param);
    }


    public function readUser($deviceId){
        $uri = 'novelsale/openapi/user/distribution/v1';
        $param = [
            'device_id' => $deviceId
        ];

        return $this->apiRequest($uri,$param);
    }

    public function readAdInfo($deviceId){
        $uri = 'novelsale/openapi/ad/list/v1';
        $param = [
            'device_id' => $deviceId
        ];

        return $this->apiRequest($uri,$param);
    }

}
