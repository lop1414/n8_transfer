<?php

namespace App\Sdks\Yw\Traits;


trait Order
{

    public function getOrders($param){
        return $this->apiRequest('cpapi/wxRecharge/quickappchargelog',$param);
    }



    public function getOrdersByTime($startTime,$endTime,$status = null){
        $param['start_time'] = $startTime;
        $param['end_time'] = $endTime;
        if($status){
            $param['order_status'] = $status;
        }
        return $this->getOrders($param);
    }



    public function getOrdersByGuid($guid){
        $param['guid'] = $guid;
        return $this->getOrders($param);
    }





    public function getWxOrder($param){
        return $this->apiRequest('cpapi/wxRecharge/querychargelog',$param);
    }



    public function getWxOrdersByTime($startTime,$endTime,$status = null){
        $param['start_time'] = $startTime;
        $param['end_time'] = $endTime;
        if($status){
            $param['order_status'] = $status;
        }

        return $this->getWxOrder($param);
    }


    public function getOrdersByOpenId($openId){
        $param['openid'] = $openId;
        return $this->getOrders($param);
    }

}
