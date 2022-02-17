<?php

namespace App\Sdks\Bm\Traits;


trait User
{

    public function getUsers($startTime,$endTime,$page = 1){
        $uri = 'foreign/users';
        $param = [
            'regTimeStart' => strtotime($startTime),
            'regTimeEnd' => strtotime($endTime),
            'page'  => $page
        ];

        return $this->apiRequest($uri,$param);
    }

    /**
     * @param $startTime
     * @param $endTime
     * @param int $page
     * @return mixed
     * 获取加桌的用户
     */
    public function getInstallUsers($startTime,$endTime,$page = 1){
        $uri = 'foreign/users';
        $param = [
            'installTimeStart' => strtotime($startTime),
            'installTimeEnd' => strtotime($endTime),
            'page'  => $page
        ];

        return $this->apiRequest($uri,$param);
    }


    public function getChangeChannelLog($startTime,$endTime,$page = 1){
        $uri = 'foreign/flow';
        $param = [
            'startTime' => strtotime($startTime),
            'endTime' => strtotime($endTime),
            'page'  => $page
        ];

        return $this->apiRequest($uri,$param);
    }

}
