<?php

namespace App\Services;


use App\Common\Enums\MatcherEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Services\BaseService;
use App\Common\Services\ConsoleEchoService;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Common\Tools\CustomRedis;
use App\Models\UserActionLogModel;
use App\Sdks\N8\N8Sdk;
use App\Services\AdvClick\SaveClickDataService;


class PullUserActionBaseService extends BaseService
{


    /**
     * @var
     * 行为类型
     */
    protected $actionType;

    /**
     * @var
     * 数据来源
     */
    protected $source;

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


    protected $saveClickDataService;


    /**
     * @var N8Sdk
     */
    protected $n8Sdk;



    public function __construct(){
        parent::__construct();
        $this->model = new UserActionLogModel();
        $this->echoService = new ConsoleEchoService();
        $this->n8Sdk = new N8Sdk();
        $this->saveClickDataService = new SaveClickDataService();
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


    public function setSource($source){
        $this->source = $source;
        return $this;
    }


    public function getSource(){
        return $this->source;
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
        $this->endTime = min($endTime,date('Y-m-d H:i:s'));
    }



    /**
     * @param $info
     * @return $this
     * 设置产品
     */
    public function setProduct($info){
        $this->product = $info;
        return $this;
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
                    $this->updateItem($item);
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
     * @param $item
     * 拉取后更新
     */
    public function updateItem($item){}



    /**
     * @param $adv
     * @param $data
     * @throws CustomException
     * 保存广告点击数据
     */
    public function saveAdvClickData($adv,$data){
        $this->saveClickDataService->saveAdvClickData($adv,$data);
    }





    /**
     * @param $data
     * @param $rawData
     * 保存数据入库
     */
    public function save($data,$rawData){
        $matcher =  $data['matcher'] ?? MatcherEnum::SYS;
        $this->model->setTableNameWithMonth($data['action_time'])->create([
            'product_id'    => $this->product['id'],
            'open_id'       => $data['open_id'],
            'action_time'   => $data['action_time'],
            'type'          => $this->actionType,
            'cp_channel_id' => $data['cp_channel_id'],
            'request_id'    => $data['request_id'],
            'ip'            => $data['ip'],
            'extend'        => $data['extend'],
            'data'          => $rawData,
            'status'        => ReportStatusEnum::WAITING,
            'action_id'     => $data['action_id'] ?? '',
            'matcher'       => $matcher,
            'source'        => $this->source,
        ]);

    }

    /**
     * @param $data
     * @param $rawData
     * 更新保存
     */
    public function updateSave($data,$rawData){
        $info = $this->model
            ->setTableNameWithMonth($data['action_time'])
            ->where('product_id',$this->product['id'])
            ->where('open_id',$data['open_id'])
            ->where('action_time',$data['action_time'])
            ->where('type',$this->actionType)
            ->first();

        if(empty($info) || $info->status == ReportStatusEnum::DONE){
            $this->save($data,$rawData);
            return ;
        }

        // 更新信息
        if(empty($info->cp_channel_id) && !empty($item['cp_channel_id'])){
            $info->cp_channel_id = $item['cp_channel_id'];
        }

        if((empty($info->ip) || $info->ip == '-') && !empty($item['ip'])){
            $info->ip = $item['ip'];
        }

        if(empty($info->ua) && !empty($item['ua'])){
            $info->ua = $item['ua'];
        }

        $info->save();
    }


    /**
     * @param $data
     * @return array
     * 过滤扩展信息
     */
    public function filterExtendInfo($data){
        return array(
            'click_id'              => $data['click_id'] ?? '',
            'ua'                    => $data['ua'] ?? '',
            'muid'                  => $data['muid'] ?? '',
            'oaid'                  => $data['oaid'] ?? '',
            'device_brand'          => $data['device_brand'] ?? '',
            'device_manufacturer'   => $data['device_manufacturer'] ?? '',
            'device_model'          => $data['device_model'] ?? '',
            'device_product'        => $data['device_product'] ?? '',
            'device_os_version_name'=> $data['device_os_version_name'] ?? '',
            'device_os_version_code'=> $data['device_os_version_code'] ?? '',
            'device_platform_version_name' => $data['device_platform_version_name'] ?? '',
            'device_platform_version_code' => $data['device_platform_version_code'] ?? '',
            'android_id'            => $data['android_id'] ?? ''
        );
    }




    /**
     * @param $data
     * @return bool
     * 是否重复加桌
     */
    public function isRepeatAddShortcut($data){
        $key = $this->getLogKey($data);
        $customRedis = new CustomRedis();
        $info = $customRedis->get($key);
        return !!$info;
    }


    /**
     * @param $data
     * 设置加桌缓存记录
     */
    public function setAddShortcutCacheLog($data){
        $key = $this->getLogKey($data);
        $customRedis = new CustomRedis();
        $customRedis->set($key,1);
        $customRedis->expire($key,7200);
    }

    /**
     * @param $data
     * @return string
     * 后期缓存下标
     */
    public function getLogKey($data){
        $keyArr = ['user_add_shortcut_log',$data['product_id'],$data['open_id'],$data['cp_channel_id'],$data['action_id']];
        return implode(':',$keyArr);
    }


    /**
     * @param $ip
     * @return bool
     * 是否为ip v6
     */
    public function isIpv6($ip){
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            return true;
        } else {
            return false;
        }
    }

}
