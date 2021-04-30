<?php

namespace App\Sdks\N8\Traits;



trait ReportUserAction
{



    /**
     * 用户注册行为
     *
     * @param $param
     * @return mixed
     */
    public function reportReg($param){

        $uri = '/open/action_report/reg';

        return $this->apiRequest($uri,$param,'POST');
    }



    /**
     * 用户阅读行为
     *
     * @param $param
     * @return mixed
     */
    public function reportRead($param){

        $uri = '/open/action_report/read';

        return $this->apiRequest($uri,$param,'POST');
    }



    /**
     * 用户登陆行为
     *
     * @param $param
     * @return mixed
     */
    public function reportLogin($param){

        $uri = '/open/action_report/login';

        return $this->apiRequest($uri,$param,'POST');
    }



    /**
     * 用户加桌行为
     *
     * @param $param
     * @return mixed
     */
    public function reportAddShortcut($param){

        $uri = '/open/action_report/add_shortcut';

        return $this->apiRequest($uri,$param,'POST');
    }



    /**
     * 用户关注行为
     *
     * @param $param
     * @return mixed
     */
    public function reportFollow($param){

        $uri = '/open/action_report/follow';

        return $this->apiRequest($uri,$param,'POST');
    }


    /**
     * 用户下单行为
     *
     * @param $param
     * @return mixed
     */
    public function reportOrder($param){

        $uri = '/open/action_report/order';

        return $this->apiRequest($uri,$param,'POST');
    }

    /**
     * 订单完成行为
     *
     * @param $param
     * @return mixed
     */
    public function reportOrderComplete($param){

        $uri = '/open/action_report/order_complete';

        return $this->apiRequest($uri,$param,'POST');
    }

}
