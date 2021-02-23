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

        $uri = '/open/action_report/reg';

        return $this->apiKyyRequest($uri,$param,'POST');
    }


    /**
     * 用户绑定渠道记录
     *
     * @param $param
     * @return mixed
     */
    public function reportKyyUserBindChannel($param){

        $uri = '/open/action_report/bind_channel';

        return $this->apiKyyRequest($uri,$param,'POST');
    }


    /**
     * 用户加桌行为
     *
     * @param $param
     * @return mixed
     */
    public function reportKyyUserShortcut($param){

        $uri = '/open/action_report/add_shortcut';

        return $this->apiKyyRequest($uri,$param,'POST');
    }


    /**
     * 用户下单行为
     *
     * @param $param
     * @return mixed
     */
    public function reportKyyUserOrder($param){

        $uri = '/open/action_report/order';

        return $this->apiKyyRequest($uri,$param,'POST');
    }

    /**
     * 订单完成行为
     *
     * @param $param
     * @return mixed
     */
    public function reportKyyOrderComplete($param){

        $uri = '/open/action_report/order_complete';

        return $this->apiKyyRequest($uri,$param,'POST');
    }

}
