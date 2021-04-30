<?php

namespace App\Services;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\MatcherEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Enums\ResponseCodeEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\ConsoleEchoService;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Models\UserActionLogModel;
use App\Sdks\N8\N8Sdk;


class UserActionBaseService extends BaseService
{


    /**
     * @var
     * 行为类型
     */
    protected $actionType;

    /**
     * @var N8Sdk
     */
    protected $n8Sdk;

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
     * @var bool
     * 上报不完整数据
     */
    protected $reportIncompleteData = false;


    public $echoService;


    protected $clickService;



    public function __construct(){
        parent::__construct();
        $this->echoService = new ConsoleEchoService();
        $this->n8Sdk = new N8Sdk();
        $this->model = new UserActionLogModel();

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
     * 开启上报不完整数据
     */
    public function openReportIncompleteData(){
        $this->reportIncompleteData = true;
    }


    /**
     * 拉 预处理
     */
    public function pullPrepare(){}


    /**
     * 拉取后
     */
    public function pullAfter(){}



    public function pull(){
        $list = $this->pullPrepare();
        foreach ($list as $item){

            try{
                $this->pullItem($item);

            }catch (\Exception $e){

                //未命中唯一索引
                if($e->getCode() != 23000){
                    //日志
                    (new ErrorLogService())->catch($e);
                    var_dump($e);
                }else{
                    echo "  命中唯一索引\n";
                }
            }
        }
        $this->pullAfter();
    }


    public function pullItem($item){}





    public function push(){
        $list = $this->getUserActionList();

        $action = 'report';
        $action .= ucfirst(Functions::camelize($this->actionType));
        foreach ($list as $item){
            $tmp = $this->pushItem($item);
            $res = $this->n8Sdk->$action($tmp);

            if($res['code'] == ResponseCodeEnum::SUCCESS){
                $item->status = ReportStatusEnum::DONE;
            }else{
                $item->status = ReportStatusEnum::FAIL;
            }
            $item->save();
        }
    }



    public function pushItem($item){
        return [];
    }




    /**
     * @param $data
     * @param $rawData
     * 保存数据入库
     */
    public function save($data,$rawData){

        $this->model->setTableNameWithMonth($data['action_time'])->create([
            'product_id'    => $this->product['id'],
            'open_id'       => $data['open_id'],
            'action_time'   => $data['action_time'],
            'type'          => $this->actionType,
            'cp_channel_id' => $data['cp_channel_id'],
            'request_id'    => $data['request_id'],
            'ip'            => $data['ip'],
            'data'          => $rawData,
            'status'        => ReportStatusEnum::WAITING,
            'action_id'     => $data['action_id'] ?? '',
            'matcher'       => $data['matcher'] ?? MatcherEnum::SYS,
        ]);

    }



    /**
     * @return mixed
     * 获取需要上报行为数据列表
     */
    public function getUserActionList($fn = null){

        return $this->model
            ->setTableNameWithMonth($this->startTime)
            ->whereBetween('action_time',[$this->startTime,$this->endTime])
            ->where('product_id',$this->product['id'])
            ->where('type',$this->actionType)
            ->where('status',ReportStatusEnum::WAITING)
            ->when(!$this->reportIncompleteData,function ($query){
                return $query
                    ->where('ip','!=','');
            })
            ->when($fn,function ($query) use ($fn){
                return $fn($query);
            })
            ->get();
    }


    /**
     * @param $adv
     * @param $data
     * @throws CustomException
     * 保存广告点击数据
     */
    public function saveAdvClickData($adv,$data){
        $service = $this->getClickService($adv);
        $service->save($data);
    }



    /**
     * @param $adv
     * @return mixed
     * @throws CustomException
     * 分发各广告商service
     */
    public function getClickService($adv){
        if(empty($this->clickService[$adv])){
            Functions::hasEnum(AdvAliasEnum::class,$adv);

            $action = ucfirst(Functions::camelize($adv));
            $class = "App\Services\AdvClick\\{$action}ClickService";

            if(!class_exists($class)){
                throw new CustomException([
                    'code' => 'NOT_FOUND_CLASS',
                    'message' => "未知的类:{$class}",
                ]);
            }

            $this->clickService[$adv] = new $class;
        }

        return $this->clickService[$adv];
    }


}
