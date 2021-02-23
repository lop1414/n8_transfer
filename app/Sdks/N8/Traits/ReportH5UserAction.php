<?php

namespace App\Sdks\N8\Traits;



trait ReportH5UserAction
{



    /**
     * 用户注册行为
     *
     * @param $param
     * @return mixed
     */
    public function reportH5UserReg($param){

        $uri = '/open/action_report/reg';

        return $this->apiH5Request($uri,$param,'POST');
    }


    /**
     * 用户绑定渠道记录
     *
     * @param $param
     * @return mixed
     */
    public function reportH5UserBindChannel($param){

        $uri = '/open/action_report/bind_channel';

        return $this->apiH5Request($uri,$param,'POST');
    }


    /**
     * 用户关注行为
     *
     * @param $param
     * @return mixed
     */
    public function reportH5UserFollow($param){

        $uri = '/open/action_report/follow';

        return $this->apiH5Request($uri,$param,'POST');
    }


    /**
     * 用户下单行为
     *
     * @param $param
     * @return mixed
     */
    public function reportH5UserOrder($param){

        $uri = '/open/action_report/order';

        return $this->apiH5Request($uri,$param,'POST');
    }

    /**
     * 订单完成行为
     *
     * @param $param
     * @return mixed
     */
    public function reportH5OrderComplete($param){

        $uri = '/open/action_report/order_complete';

        return $this->apiH5Request($uri,$param,'POST');
    }

}
