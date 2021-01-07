<?php

namespace App\Sdks\N8\Traits;



trait ReportKyyUserAction
{



    /**
     * 用户行为数据
     *
     * @param $param
     * @return mixed
     */
    public function reportKyyUserReg($param){

        $uri = '/open/kyy/action_report/reg';

        return $this->apiRequest($uri,$param,'POST');
    }

}
