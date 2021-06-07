<?php
/**
 * 匹配数据处理 更新注册行为数据
 */
namespace App\Services\YwKyy;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Services\BaseService;
use App\Enums\UserActionTypeEnum;
use App\Models\UserActionLogModel;
use App\Services\AdvClick\SaveClickDataService;


class MatchDataService extends BaseService
{

    protected $userActionLogModel;

    protected $saveClickDataService;


    protected $date;

    public function __construct()
    {
        parent::__construct();
        $this->userActionLogModel = new UserActionLogModel();
        $this->saveClickDataService = new SaveClickDataService();
        $this->date = date('Y-m-d');
    }



    public function ocean($data){
        $cpChannelId = $data['channel_id'] ?: '';
        $info = $this->getRegLogInfo($data['product_id'],$data['guid'],$cpChannelId);
        if(empty($info)) return;

        $requestId = $info['request_id'] ?: 'n8_'.md5(uniqid());
        $this->saveClickDataService
            ->saveAdvClickData(AdvAliasEnum::OCEAN,[
                'ip'           => $info['ip'],
                'ua'           => $info['extend']['ua'],
                'muid'         => $data['url_info']['muid'] ?? '',
                'android_id'   => '',
                'oaid'         => $data['oaid'] ?? '',
                'oaid_md5'     => '',
                'os'           => $data['url_info']['os'] ?? '',
                'click_at'     => $info['action_time'],
                'ad_id'        => $data['aid'],
                'creative_id'  => $data['cid'],
                'creative_type'=> '',
                'link'         => $data['decode_url'],
                'request_id'   => $requestId,
                'open_id'      => $data['guid'],
                'action_id'    => $data['guid'],
                'type'         => UserActionTypeEnum::REG,
            ]);

        $info->request_id = $requestId;
        if(!empty($cpChannelId)){
            $info->cp_channel_id = $cpChannelId;
        }
        $info->save();
    }



    public function kuaiShou($data){
        $cpChannelId = $data['channel_id'] ?: '';
        $info = $this->getRegLogInfo($data['product_id'],$data['guid'],$cpChannelId);
        if(empty($info)) return;

        $requestId = $info['request_id'] ?: 'n8_'.md5(uniqid());
        $this->saveClickDataService
            ->saveAdvClickData(AdvAliasEnum::KUAI_SHOU,[
                'ip'           => $info['ip'],
                'ua'           => $info['extend']['ua'],
                'click_at'     => $info['action_time'],
                'request_id'   => $requestId,
                'type'         => UserActionTypeEnum::REG,
                'rawData'      => $data
            ]);

        $info->request_id = $requestId;
        if(!empty($cpChannelId)){
            $info->cp_channel_id = $cpChannelId;
        }
        $info->save();
    }




    /**
     * @param $productId
     * @param $openId
     * @param $cpChannelId
     * @return mixed
     * 获取用户注册记录(最近2月)
     */
    public function getRegLogInfo($productId,$openId,$cpChannelId){
        $info = $this->userActionLogModel
            ->setTableNameWithMonth($this->date)
            ->where('type',UserActionTypeEnum::REG)
            ->where('product_id',$productId)
            ->where('open_id',$openId)
            ->where('cp_channel_id',$cpChannelId)
            ->first();

        if(empty($info)){
            $date = date('Y-m-d',strtotime('-1 month',strtotime($this->date)));
            $info = $this->userActionLogModel
                ->setTableNameWithMonth($date)
                ->where('type',UserActionTypeEnum::REG)
                ->where('product_id',$productId)
                ->where('open_id',$openId)
                ->where('cp_channel_id',$cpChannelId)
                ->first();
        }

        return $info;
    }

}
