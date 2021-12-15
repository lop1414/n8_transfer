<?php

namespace App\Sdks\Fq\Traits;



trait Channel
{



    public function getChannelList($startTime,$endTime,$offset = 0, $limit = 1000){
        $uri = 'novelsale/openapi/promotion/list/v1';
        $param = [
            'begin' => strtotime($startTime),
            'end' => strtotime($endTime),
            'limit'  => $limit,
            'offset' => $offset
        ];

        return $this->apiRequest($uri,$param);
    }

    public function readChannel($id){
        $uri = 'novelsale/openapi/promotion/list/v1';
        $param = [
            'promotion_id'  => $id,
        ];

        return $this->apiRequest($uri,$param);
    }

}
