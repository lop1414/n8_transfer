<?php

namespace App\Spiders\Yw\Traits;


trait Spread
{

    public function getSpreadPromotionList($startDate,$endDate,$recycle = 0){
        $uri = 'spread/getPromotionList';
        $param = [
            'type'      => 0,
            'channeltype' => 1,
            'startdate' => $startDate,
            'enddate'   => $endDate,
            'recycle'   => $recycle,
            'name'      => '',
            'id'        => '',
            'p'         => '',
            'page_name' => ''
        ];
        return $this->apiRequest($uri,$param);
    }

}
