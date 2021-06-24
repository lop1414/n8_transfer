<?php

namespace App\Services;


use App\Common\Enums\MatcherEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Enums\UserActionTypeEnum;
use App\Models\UserActionLogModel;
use App\Sdks\N8\N8Sdk;


class PushUserActionService extends BaseService
{


    /**
     * @var
     * 行为类型
     */
    protected $actionType;



    /**
     * @var
     * 时间区间
     */
    protected $startTime,$endTime;


    /**
     * @var
     * 产品信息
     */
    protected $product;




    /**
     * @var N8Sdk
     */
    protected $n8Sdk;


    /**
     * @var float|int
     * 上报没有渠道的注册行为 时间差 （没有渠道等1小时后再报）
     */
    protected $reportNoChannelDiffTime = 60 * 60;


    /**
     * @var
     * 产品映射
     */
    protected $productMap;


    public function __construct(){
        parent::__construct();
        $this->model = new UserActionLogModel();
        $this->n8Sdk = new N8Sdk();

    }


    public function getReportNoChannelDiffTime(){
        return $this->reportNoChannelDiffTime;
    }


    public function setActionType($type){
        $this->actionType = $type;
        return $this;

    }

    /**
     * @return mixed
     */
    public function getActionType(){
        return $this->actionType;
    }



    /**
     * @param $startTime
     * @param $endTime
     * @throws CustomException
     * 设置时间区间
     */
    public function setTimeRange($startTime,$endTime){
        if(date('m',strtotime($startTime)) != date('m',strtotime($startTime))){
            throw new CustomException([
                'code' => 'DATE_TIME_ERROR',
                'message' => '月份不一致',
            ]);
        }
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }



    /**
     * @param $info
     * 设置产品
     */
    public function setProduct($info){
        $this->product = $info;
    }



    public function setProductMap(){
        $product = (new ProductService())->get();
        $this->productMap = array_column($product,null,'id');
    }


    /**
     * 上报
     */
    public function push(){

        $list = $this->getReportUserActionList();

        $this->setProductMap();

        foreach ($list as $item){

          $this->pushItem($item);
        }
    }


    /**
     * @throws CustomException
     * 上报所有
     */
    public function pushAll(){
        $monthList = Functions::getMonthListByRange(['2019-10-01',date('Y-m-d')],'Y-m-01 00:00:00');

        foreach ($monthList as $month){
            echo $month. "\n";
            do{
                $list =  $this->model
                    ->setTableNameWithMonth($month)
                    ->where('product_id',$this->product['id'])
                    ->where('type',$this->actionType)
                    ->where('status',ReportStatusEnum::WAITING)
                    ->skip(0)
                    ->take(1000)
                    ->orderBy('action_time')
                    ->get();

                foreach ($list as $item){

                    $this->pushItem($item);
                }
            }while(!$list->isEmpty());
        }
    }


    protected function pushItem($item){
        try{
            // 注册行为
            if($this->actionType == UserActionTypeEnum::REG){
                //没有渠道
                if(empty($item['cp_channel_id'])){
                    //时间差
                    $diff = time() - strtotime($item['action_time']);
                    if($diff <= $this->reportNoChannelDiffTime){
                        return;
                    }
                }

                //不是系统匹配且没有request_id
                if($item['matcher'] != MatcherEnum::SYS && empty($item['request_id'])){
                    //时间差
                    $diff = time() - strtotime($item['created_at']);
                    if($diff < 60*60*2){
                        return;
                    }
                }
            }
            $action = 'report';
            $action .= ucfirst(Functions::camelize($this->actionType));
            $pushData = array_merge($item['extend'],[
                'product_alias' => $this->product['cp_product_alias'],
                'cp_type'       => $this->product['cp_type'],
                'open_id'       => $item['open_id'],
                'action_time'   => $item['action_time'],
                'cp_channel_id' => $item['cp_channel_id'],
                'ip'            => $item['ip'],
                'request_id'    => $item['request_id']
            ]);

            $this->n8Sdk->setSecret($this->productMap[$item['product_id']]['secret']);
            $this->n8Sdk->$action($pushData);
            $item->status = ReportStatusEnum::DONE;

        }catch(CustomException $e){
            $errorInfo = $e->getErrorInfo(true);

            $item->fail_data = $errorInfo;
            $item->status = ReportStatusEnum::FAIL;
            echo $errorInfo['message']. "\n";

        }catch(\Exception $e){

            $errorInfo = [
                'code'      => $e->getCode(),
                'message'   => $e->getMessage()
            ];

            $item->fail_data = $errorInfo;
            $item->status = ReportStatusEnum::FAIL;
            echo $e->getMessage(). "\n";
        }

        $item->save();
    }




    /**
     * @return mixed
     * 获取需要上报行为数据列表
     */
    public function getReportUserActionList(){

        return $this->model
            ->setTableNameWithMonth($this->startTime)
            ->whereBetween('action_time',[$this->startTime,$this->endTime])
            ->where('product_id',$this->product['id'])
            ->where('type',$this->actionType)
            ->where('status',ReportStatusEnum::WAITING)
            ->orderBy('action_time')
            ->get();
    }

}
