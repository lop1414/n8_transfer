<?php

namespace App\Sdks\Fq\Traits;


trait User
{

    public function getUserList($startTime,$endTime,$page = 0, $pageSize = 100){
        $uri = 'novelsale/openapi/user/list/v1';
        $param = [
            'begin' => strtotime($startTime),
            'end' => strtotime($endTime),
            'show_not_recharge' => true,
            'page_size'  => $pageSize,
            'page_index' => $page
        ];

        return $this->apiRequest($uri,$param);
    }



    public function getUsers($startTime,$endTime,$offset = 0, $limit = 100){
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
