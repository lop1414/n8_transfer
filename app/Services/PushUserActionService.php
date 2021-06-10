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



    public function __construct(){
        parent::__construct();
        $this->model = new UserActionLogModel();
        $this->n8Sdk = new N8Sdk();

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





    /**
     * 上报
     */
    public function push(){

        $list = $this->getReportUserActionList();

        $product = (new ProductService())->get();
        $productMap = array_column($product,null,'id');

        foreach ($list as $item){

            try{
                // 注册行为
                if($this->actionType == UserActionTypeEnum::REG
                    && (empty($item->cp_channel_id) || empty($item->request_id) )){

                    //没有渠道 or 不是系统匹配且没有request_id
                    if(
                        empty($item->cp_channel_id)
                        ||  ($item->matcher != MatcherEnum::SYS && empty($item->request_id))
                    ){
                        //时间差
                        $diff = time() - strtotime($item->created_at);
                        if($diff < 60*60*2){
                            continue;
                        }
                    }
                }
                $action = 'report';
                $action .= ucfirst(Functions::camelize($this->actionType));
                $pushData = array_merge([
                    'product_alias' => $this->product['cp_product_alias'],
                    'cp_type'       => $this->product['cp_type'],
                    'open_id'       => $item['open_id'],
                    'action_time'   => $item['action_time'],
                    'cp_channel_id' => $item['cp_channel_id'],
                    'ip'            => $item['ip'],
                    'request_id'    => $item['request_id']
                ],$item['extend']);

                $this->n8Sdk->setSecret($productMap[$item['product_id']]['secret']);
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
