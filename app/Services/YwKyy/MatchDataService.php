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


    protected $dateRange;

    public function __construct()
    {
        parent::__construct();
        $this->userActionLogModel = new UserActionLogModel();
        $this->saveClickDataService = new SaveClickDataService();
        $this->dateRange = [
            'start' => date('Y-m-d',strtotime('-15 day')),
            'end'   => date('Y-m-d')
        ];
    }



    public function ocean($data){
        $cpChannelId = $data['cp_channel_id'] ?: '';
        $info = $this->getRegLogInfo($data['product_id'],$data['open_id']);
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
                'ad_id'        => $data['data']['raw_data']['aid'],
                'creative_id'  => $data['data']['raw_data']['cid'],
                'creative_type'=> '',
                'link'         => '',
                'request_id'   => $requestId,
                'open_id'      => $data['open_id'],
                'action_id'    => $data['open_id'],
                'type'         => UserActionTypeEnum::REG,
                'extends'      => [
                    'match_data_id' => $data['match_data_id']
                ]
            ]);

        $info->request_id = $info['request_id'];
        if(!empty($cpChannelId)){
            $info->cp_channel_id = $cpChannelId;
        }
        $info->save();
    }



    public function kuaiShou($data){

        $cpChannelId = $data['cp_channel_id'] ?: '';
        $info = $this->getRegLogInfo($data['product_id'],$data['open_id']);
        if(empty($info)) return;

        $requestId = $info['request_id'] ?: 'n8_'.md5(uniqid());
        $data['data']['request_id'] = $requestId;
        $data['data']['match_data_id'] = $data['match_data_id'];
        $this->saveClickDataService
            ->saveAdvClickData(AdvAliasEnum::KUAI_SHOU,[
                'ip'           => $info['ip'],
                'ua'           => $info['extend']['ua'],
                'click_at'     => $info['action_time'],
                'type'         => UserActionTypeEnum::REG,
                'product_id'   => $data['product_id'],
                'extends'      => $data['data'],
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
     * @return mixed
     * 获取用户注册记录(最近2月)
     */
    public function getRegLogInfo($productId,$openId){
        $info = $this->userActionLogModel
            ->setTableNameWithMonth($this->dateRange['start'])
            ->where('type',UserActionTypeEnum::REG)
            ->where('product_id',$productId)
            ->where('open_id',$openId)
            ->whereBetween('action_time',$this->dateRange)
            ->first();

        if(empty($info)
            && date('Y-m',strtotime($this->dateRange['start'])) != date('Y-m',strtotime($this->dateRange['end']))
        ){

            $info = $this->userActionLogModel
                ->setTableNameWithMonth($this->dateRange['end'])
                ->where('type',UserActionTypeEnum::REG)
                ->where('product_id',$productId)
                ->where('open_id',$openId)
                ->whereBetween('action_time',$this->dateRange)
                ->first();
        }

        return $info;
    }

}
