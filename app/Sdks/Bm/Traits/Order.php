<?php

namespace App\Sdks\Bm\Traits;


trait Order
{

    public function getOrders($startTime,$endTime,$page = 1){
        $uri = 'foreign/orders';
        $param = [
            'createTimeStart' => strtotime($startTime),
            'createTimeEnd' => strtotime($endTime),
            'orderStatus'   => 2,
            'page'  => $page
        ];
        return $this->apiRequest($uri,$param);
    }


    /**
     * @param $startTime
     * @param $endTime
     * @param int $page
     * @return mixed
     * 获取已支付订单
     */
    public function getPayOrders($startTime,$endTime,$page = 1){
        $uri = 'foreign/orders';
        $param = [
            'payTimeStart' => strtotime($startTime),
            'payTimeEnd' => strtotime($endTime),
            'page'  => $page
        ];
        return $this->apiRequest($uri,$param);
    }

}
