<?php

namespace App\Services;

use App\Common\Services\BaseService;
use App\Common\Services\ConsoleEchoService;

use App\Common\Services\SystemApi\UnionApiService;
use App\Sdks\N8\N8Sdk;
use App\Traits\AnalogPush\TwKyy;
use App\Traits\AnalogPush\YwKyy;


class AnalogPushService extends BaseService
{
    use YwKyy;
    use TwKyy;

    public $pushSdk;

    /**
     * 时间循环间隔
     *
     * @var int
     */
    protected $timeInterval = 60;


    public $echoService;

    /**
     * 时间区间
     *
     * @var
     */
    protected $statDate,$endDate;

    public function __construct(){
        $this->pushSdk = new N8Sdk();
        $this->echoService = new ConsoleEchoService();
    }


    /**
     * 设置时间区间
     *
     * @param $statDate
     * @param $endDate
     */
    public function setTimeRange($statDate,$endDate){
        $this->statDate = $statDate;
        $this->endDate = $endDate;
    }

    /**
     * 设置时间循环间隔
     *
     * @param $int
     */
    public function setTimeInterval($int){
        $this->timeInterval = $int;
    }


    /**
     * 获取产品列表
     *
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     */
    public function getProductList(){
        return  (new UnionApiService())->apiGetProduct();
    }


    /**
     * 根据时间段 循环
     *
     * @param $fn
     */
    public function loopTime($fn){
        $date = $this->statDate;

        while($date <= $this->endDate){
            $tmpEndDate = date('Y-m-d H:i:s',  strtotime($date) + $this->timeInterval);

            $fn($date,$tmpEndDate);

            $date = $tmpEndDate;

        }
    }


}
