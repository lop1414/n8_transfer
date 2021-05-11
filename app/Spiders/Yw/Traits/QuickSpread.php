<?php

namespace App\Spiders\Yw\Traits;


trait QuickSpread
{

    public function getQuickSpreadPromotionList($startDate,$endDate,$page = 1,$recycle = 0){
        $uri = 'QuickSpread/getPromotionList';
        $param = [
            'type'      => 2,
            'startdate' => $startDate,
            'enddate'   => $endDate,
            'recycle'   => $recycle,
            'name'      => '',
            'id'        => '',
            'p'         => $page,
            'page_name' => ''
        ];
        return $this->apiRequest($uri,$param);
    }

}
