<?php

namespace App\Sdks\SecondVersion\Traits;



trait UserAction
{



    /**
     * 用户行为数据
     *
     * @param $appflag
     * @param $plfAlias
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getUserAction($appflag,$plfAlias,$startTime,$endTime){

        $uri = '/api/user_action';

        $param = [
            'appflag'       => $appflag,
            'plf_alias'     => $plfAlias,
            'start_date'    => $startTime,
            'end_date'      => $endTime,
        ];

        return $this->apiRequest($uri,$param,'POST');
    }

}
