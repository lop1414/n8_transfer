<?php

namespace App\Sdks\SecondVersion\Traits;



trait Channel
{



    /**
     * 用户注册行为数据
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
            'type'          => 'ACTIVATION',
            'start_date'    => $startTime,
            'end_date'      => $endTime,
        ];

        return $this->apiRequest($uri,$param,'POST');
    }



}
