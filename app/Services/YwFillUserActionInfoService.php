<?php
/**
 * 补充用户行为信息 临时脚本
 */
namespace App\Services;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Services\BaseService;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Models\UserActionLogModel;
use App\Sdks\Yw\YwSdk;
use App\Services\YwH5\UserFollowActionService;


class YwFillUserActionInfoService extends BaseService
{

    protected $product;

    protected $ywSdk;

    protected $channelMap;

    protected $userFollowActionService;




    public function __construct(){
        parent::__construct();
        $this->userFollowActionService = new UserFollowActionService();
    }

    public function setProduct($product){
        $this->product = $product;
        $this->product['cp_account'] = (new ProductService())->readCpAccount($this->product['cp_account_id']);
        $this->ywSdk = new YwSdk($this->product['cp_product_alias'],$this->product['cp_account']['account'],$this->product['cp_account']['cp_secret']);
        $this->userFollowActionService->setProduct($this->product);
        return $this;
    }



    /**
     * 渠道
     * @param $startTime
     * @param $endTime
     */
    public function cpChannelId($startTime,$endTime){
        $time = $startTime;
        $userActionLogModel = new UserActionLogModel();
        $saveActionService = (new PullUserActionBaseService())
            ->setProduct($this->product)
            ->setActionType(UserActionTypeEnum::REG)
            ->setSource(DataSourceEnums::CP_PULL);

        $reportNoChannelDiffTime = (new PushUserActionService())->getReportNoChannelDiffTime();
        while($time < $endTime){
            $tmpEndTime = date('Y-m-d H:i:s',  strtotime($time) + 60*5);
            $tmpEndTime = min($tmpEndTime,date('Y-m-d H:i:s'));

            echo("时间 : {$time} ~ {$tmpEndTime} \n");

            $para = [
                'start_time' => strtotime($time),
                'end_time'  => strtotime($tmpEndTime),
                'page'      => 1
            ];
            $currentCount = 0;
            do{
                if($this->product['type'] == ProductTypeEnums::KYY){
                    $tmp = $this->ywSdk->getUser($para);
                    $regTimeField = 'reg_time';
                    $openIdField = 'guid';
                }else{
                    $tmp = $this->ywSdk->getWxUser($para);
                    $regTimeField = 'create_time';
                    $openIdField = 'openid';
                }

                $count = $tmp['total_count'];
                $currentCount += count($tmp['list']);
                echo '总数：'.count($tmp['list'])."\n";
                foreach($tmp['list'] as $cpUser){
                    try{
                        // 微信H5
                        if($this->product['type'] == ProductTypeEnums::H5 && $cpUser['is_subscribe'] == 1){
                            $this->fillFollowAction($cpUser[$openIdField],$cpUser);
                        }

                        //没有渠道
                        $cpChannelId = $cpUser['channel_id'];
                        if(empty($cpChannelId)){
                            echo "没有渠道\n";
                            continue;
                        }

                        $tmpUser = $userActionLogModel
                            ->setTableNameWithMonth($cpUser[$regTimeField])
                            ->where('open_id',strval($cpUser[$openIdField]))
                            ->where('product_id',$this->product['id'])
                            ->where('type',UserActionTypeEnum::REG)
                            ->where('cp_channel_id','')
                            ->get();

                        foreach ($tmpUser as $modelUser){
                            if(!empty($modelUser->cp_channel_id)){
                                echo "已有渠道，无需补充\n";
                                continue;
                            }

                            //时间差 小于一个小时 行为还未上报 更新数据
                            $diff = time() - strtotime($modelUser['action_time']);
                            if($diff <= $reportNoChannelDiffTime){
                                $modelUser->cp_channel_id = $cpUser['channel_id'];
                                $modelUser->status = ReportStatusEnum::WAITING;
                                $modelUser->save();
                                echo "渠道更新:".$modelUser->open_id. "\n";
                            }else{
                                $data = $modelUser->toArray();
                                $data['cp_channel_id'] = $cpUser['channel_id'];
                                $cpUser['action_log_id'] = $data['id'];
                                $saveActionService->save($data,$cpUser);

                                echo "创建新的注册行为:".$modelUser->open_id. "\n";
                            }

                        }
                        // 推送二版
                        $repData = [
                            'open_id'   => $cpUser[$openIdField],
                            'plf_alias'   => CpTypeEnums::YW,
                            'appflag'   => $cpUser['appflag'],
                            'channel_id'=> $cpUser['channel_id'],
                        ];
                        $url = 'http://ny.7788zongni.com/api/fill_user_channel?'. http_build_query($repData);
                        file_get_contents($url);
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
                $para['page'] += 1;
                $para['next_id'] = $tmp['next_id'];
            }while($currentCount < $count);


            $time = $tmpEndTime;
        }
    }



    protected function fillFollowAction($openId,$cpUser){
        try{
            echo "补关注行为:{$openId} \n";
            $this->userFollowActionService->pullItem($cpUser);

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
                echo "  关注行为命中唯一索引 \n";
            }
        }
    }

}
