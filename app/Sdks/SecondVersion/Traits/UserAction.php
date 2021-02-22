<?php

namespace App\Sdks\SecondVersion\Traits;



trait UserAction
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
    public function getUserRegAction($appflag,$plfAlias,$startTime,$endTime){

        $uri = '/api/user_action';

        $param = [
            'appflag'       => $appflag,
            'plf_alias'     => $plfAlias,
            'type'          => 'ACTIVATION',
            'start_date'    => $startTime,
            'end_date'      => $endTime,
        ];

        return $this->apiRequest($uri,$param,'POST');
    }


    /**
     * 用户加桌行为数据
     *
     * @param $appflag
     * @param $plfAlias
     * @param $startTime
     * @param $endTime
     * @return mixed
     */
    public function getUserAddShortcutAction($appflag,$plfAlias,$startTime,$endTime){

        $uri = '/api/user_action';

        $param = [
            'appflag'       => $appflag,
            'plf_alias'     => $plfAlias,
            'type'          => 'REGISTER',
            'start_date'    => $startTime,
            'end_date'      => $endTime,
        ];

        return $this->apiRequest($uri,$param,'POST');
    }

}
