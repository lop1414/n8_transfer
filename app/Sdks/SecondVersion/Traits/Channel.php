<?php

namespace App\Sdks\SecondVersion\Traits;



trait Channel
{



    /**
     * 渠道信息
     *
     * @param $appflag
     * @param $plfAlias
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getChannel($appflag,$plfAlias,$startTime,$endTime){

        $uri = '/api/popularize';

        $param = [
            'appflag'       => $appflag,
            'plf_alias'     => $plfAlias,
            'start_time'    => $startTime,
            'end_time'      => $endTime,
        ];

        return $this->apiRequest($uri,$param,'POST');
    }



}
