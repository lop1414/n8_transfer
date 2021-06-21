<?php

namespace App\Sdks\Yw\Traits;


trait User
{

    public function getUser($param){
        $uri = 'cpapi/WxUserInfo/QuickAppFbQueryUserInfo';

        return $this->apiRequest($uri,$param);
    }


    public function getWxUser($param){
        $uri = 'cpapi/WxUserInfo/QueryUserInfo';

        return $this->apiRequest($uri,$param);
    }

}
