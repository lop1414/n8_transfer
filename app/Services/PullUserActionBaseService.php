<?php

namespace App\Services;


use App\Common\Services\BaseService;
use App\Common\Services\ConsoleEchoService;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Enums\UserActionPushStatusEnum;
use Illuminate\Support\Facades\DB;


class PullUserActionBaseService extends BaseService
{



    public $echoService;


    /**
     * 时间循环间隔
     *
     * @var int
     */
    protected $timeInterval = 60*5;




    /**
     * 时间区间
     *
     * @var
     */
    protected $statDate,$endDate;




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



    public function __construct(){
        parent::__construct();
        $this->echoService = new ConsoleEchoService();
    }




    /**
     * 获取产品列表
     *
     * @param array $data
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     */
    public function getProductList($data = []){
        return  (new UnionApiService())->apiGetProduct($data);
    }


    /**
     * 获取cp账户信息
     *
     * @param $id
     * @return mixed
     */
    public function readCpAccount($id){
        return  (new UnionApiService())->apiReadCpAccountInfo($id);
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




    /**
     * 保存行为数据
     *
     * @param $productId
     * @param $openId
     * @param $type
     * @param $actionTime
     * @param $rawData
     */
    public function save($productId,$openId,$type,$actionTime,$rawData){

        try{
            $table = 'user_action_logs_'.date('Ym',strtotime($actionTime));
            $status = UserActionPushStatusEnum::WAITING;

            $sql = "insert into {$table} (product_id, open_id, action_time,`type`,`data`,status) ";
            $sql .= "values ({$productId}, '{$openId}', '{$actionTime}' , '{$type}' ,? ,'{$status}')";

            $rawData = json_encode($rawData);
            DB::insert($sql,[$rawData]);
        }catch (\Exception $e){

            //未命中唯一索引
            if($e->getCode() != 23000){
                //日志
                (new ErrorLogService())->catch($e);
            }else{
                echo "  命中唯一索引\n";
            }
        }

    }


}
