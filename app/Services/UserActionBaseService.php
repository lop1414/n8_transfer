<?php

namespace App\Services;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\MatcherEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\ConsoleEchoService;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\UserActionTypeEnum;
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
     * @var
     * 时间区间
     */
    protected $startTime,$endTime;

    /**
     * @var
     * 产品信息
     */
    protected $product;


    public $echoService;


    protected $clickService;


    /**
     * @var N8Sdk
     */
    protected $n8Sdk;



    public function __construct(){
        parent::__construct();
        $this->model = new UserActionLogModel();
        $this->echoService = new ConsoleEchoService();
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
     * 拉 预处理
     */
    public function pullPrepare(){}



    public function pull(){
        $list = $this->pullPrepare();
        echo "total:".count($list)."\n";
        foreach ($list as $item){

            try{
                $this->pullItem($item);

            }catch(CustomException $e){
                (new ErrorLogService())->catch($e);

                //日志
                $errorInfo = $e->getErrorInfo(true);
                echo $errorInfo['message']. "\n";

            }catch (\Exception $e){

                //未命中唯一索引
                if($e->getCode() != 23000){
                    //日志
                    (new ErrorLogService())->catch($e);
                    echo $e->getMessage()."\n";
                }else{
                    echo "  命中唯一索引 \n";
                }

            }
        }
        $this->pullAfter();
    }


    /**
     * 拉取后
     */
    public function pullAfter(){}




    public function pullItem($item){}



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
     * 分发各广告商ClickService
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
     * push 预处理
     */
    public function pushPrepare(){}


    /**
     * 上报
     */
    public function push(){
        $this->pushPrepare();

        $list = $this->getReportUserActionList();

        $product = (new ProductService())->get();
        $productMap = array_column($product,null,'id');

        foreach ($list as $item){

            try{
                // 注册行为没有渠道
                if($this->actionType == UserActionTypeEnum::REG && empty($item->cp_chanel_id)){
                    //时间差
                    $diff = time() - strtotime($item->created_at);
                    if($diff < 60*60*2){
                        continue;
                    }
                }
                $action = 'report';
                $action .= ucfirst(Functions::camelize($this->actionType));
                $tmp = $this->pushItemPrepare($item);
                $this->n8Sdk->setSecret($productMap[$item['product_id']]['secret']);
                $this->n8Sdk->$action($tmp);
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


    public function pushItemPrepare($item){}




    /**
     * @param array $where
     * @return mixed
     * 获取需要上报行为数据列表
     */
    public function getReportUserActionList($where = []){

        return $this->model
            ->setTableNameWithMonth($this->startTime)
            ->whereBetween('action_time',[$this->startTime,$this->endTime])
            ->where('product_id',$this->product['id'])
            ->where('type',$this->actionType)
            ->where('status',ReportStatusEnum::WAITING)
            ->when($where,function ($query,$where){
                return $query->where($where);
            })
            ->orderBy('action_time')
            ->get();
    }

}
