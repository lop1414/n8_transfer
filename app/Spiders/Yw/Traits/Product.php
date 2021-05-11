<?php

namespace App\Spiders\Yw\Traits;


trait Product
{

    public function getCoopAppList($appName = ''){
        $uri = 'product/getCoopAppList';
        $param['coopid'] = $this->coopId;
        if(!empty($appName)){
            $param['appName'] = $appName;
        }
        return $this->apiRequest($uri,$param);
    }

}
