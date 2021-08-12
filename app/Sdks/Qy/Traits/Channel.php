<?php

namespace App\Sdks\Qy\Traits;


trait Channel
{



    public function getChannelList($date,$page = 1, $pageSize = 100){
        $uri = 'v1/referral/links';
        $param = [
            'date'  => $date,
            'page'  => $page,
            'size'  => $pageSize
        ];

        return $this->apiRequest($uri,$param);
    }

}
