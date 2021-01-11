<?php

namespace App\Sdks\N8\Traits;



trait ReportKyyUserAction
{



    /**
     * 用户注册行为
     *
     * @param $param
     * @return mixed
     */
    public function reportKyyUserReg($param){

        $uri = '/open/kyy/action_report/reg';

        return $this->apiRequest($uri,$param,'POST');
    }


    /**
     * 用户加桌行为
     *
     * @param $param
     * @return mixed
     */
    public function reportKyyUserShortcut($param){

        $uri = '/open/kyy/action_report/add_shortcut';

        return $this->apiRequest($uri,$param,'POST');
    }


    /**
     * 用户下单行为
     *
     * @param $param
     * @return mixed
     */
    public function reportKyyUserPay($param){

        $uri = '/open/kyy/action_report/pay';

        return $this->apiRequest($uri,$param,'POST');
    }

    /**
     * 订单完成行为
     *
     * @param $param
     * @return mixed
     */
    public function reportKyyOrderComplete($param){

        $uri = '/open/kyy/action_report/order_complete';

        return $this->apiRequest($uri,$param,'POST');
    }

}
