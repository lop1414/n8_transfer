<?php

namespace App\Sdks\Yw\Traits;


trait Spread
{

    public function getSpreads($param){
        return $this->apiRequest('cpapi/WxNovel/GetQuickSpreadList',$param);
    }

}
