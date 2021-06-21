<?php
/**
 * 匹配数据处理 更新注册行为数据
 */
namespace App\Services;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Services\BaseService;
use App\Enums\UserActionTypeEnum;
use App\Models\KuaiShouClickModel;
use App\Models\OceanClickModel;
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
        $cpChannelId = $data['cp_channel_id'];
        $info = $this->getRegLogInfo($data['product_id'],$data['open_id']);
        if(empty($info)){
            echo "ocean:没有log: {$data['open_id']} \n";
            return;
        }

        if(!empty($info['request_id'])){
            $requestId = $info['request_id'];
            $click = (new OceanClickModel())->where('request_id',$requestId)->first();
            if(!empty($click)){
                echo "ocean:更新过: {$info['open_id']} \n";
                return $info;
            }
        }else{
            $requestId = 'n8_'.md5(uniqid());
        }

        if($data['type'] == UserActionTypeEnum::SECOND_VERSION_REG){
            $clickData = array(
                'ip'           => $info['ip'],
                'ua'           => $info['extend']['ua'],
                'muid'         => '',
                'android_id'   => '',
                'oaid'         => '',
                'oaid_md5'     => '',
                'os'           => '',
                'click_at'     => $info['action_time'],
                'ad_id'        => $data['data']['link_info']['adid'],
                'creative_id'  => $data['data']['link_info']['creativeid'],
                'creative_type'=> $data['data']['link_info']['creativetype'],
                'link'         => $data['data']['link'],
                'request_id'   => $requestId,
                'open_id'      => $data['open_id'],
                'action_id'    => $data['open_id']
            );

        }else{
            $rawData = $data['data']['raw_data'];
            $clickData = array(
                'ip'           => $info['ip'],
                'ua'           => $info['extend']['ua'],
                'muid'         => $data['url_info']['muid'] ?? '',
                'android_id'   => '',
                'oaid'         => $data['oaid'] ?? '',
                'oaid_md5'     => '',
                'os'           => $data['url_info']['os'] ?? '',
                'click_at'     => $info['action_time'],
                'ad_id'        => $rawData['aid'],
                'creative_id'  => $rawData['cid'],
                'creative_type'=> '',
                'link'         => '',
                'request_id'   => $requestId,
                'open_id'      => $data['open_id'],
                'action_id'    => $data['open_id'],
                'type'         => UserActionTypeEnum::REG,
                'extends'      => [
                    'match_data_id' => $data['id']
                ]
            );
        }

        $clickData['type'] = UserActionTypeEnum::REG;
        $clickData['extends'] = [
            'match_data_id' => $data['id']
        ];



        $this->saveClickDataService->saveAdvClickData(AdvAliasEnum::OCEAN,$clickData);

        $info->request_id = $requestId;
        if(!empty($cpChannelId)){
            $info->cp_channel_id = $cpChannelId;
        }
        $info->save();
        echo "ocean:更新成功: {$info['open_id']} \n";
        return $info;
    }



    public function kuaiShou($data){
        $rawData = $data['data']['raw_data'];
        $cpChannelId = $data['cp_channel_id'];
        $info = $this->getRegLogInfo($data['product_id'],$data['open_id']);
        if(empty($info)){
            echo "kuai_shou:没有log: {$data['open_id']} \n";
            return;
        }

        if(!empty($info['request_id'])){
            $requestId = $info['request_id'];
            $click = (new KuaiShouClickModel())->where('request_id',$requestId)->first();
            if(!empty($click)){
                echo "kuai_shou:更新过: {$info['open_id']} \n";
                return $info;
            }
        }else{
            $requestId = 'n8_'.md5(uniqid());
        }

        $rawData['match_data_id'] = $data['id'];
        $this->saveClickDataService
            ->saveAdvClickData(AdvAliasEnum::KUAI_SHOU,[
                'ip'           => $info['ip'],
                'ua'           => $info['extend']['ua'],
                'click_at'     => $info['action_time'],
                'type'         => UserActionTypeEnum::REG,
                'product_id'   => $data['product_id'],
                'request_id'   => $requestId,
                'extends'      => $rawData,
            ]);

        $info->request_id = $requestId;
        if(!empty($cpChannelId)){
            $info->cp_channel_id = $cpChannelId;
        }
        $info->save();
        echo "kuai_shou:更新成功: {$info['open_id']} \n";
        return $info;

    }



    public function baiDu($data){

        $cpChannelId = $data['cp_channel_id'];
        $info = $this->getRegLogInfo($data['product_id'],$data['open_id']);
        if(empty($info)){
            echo "bai_du:没有log: {$data['open_id']} \n";
            return;
        }

        if(!empty($info['request_id'])){
            $requestId = $info['request_id'];
            $click = (new KuaiShouClickModel())->where('request_id',$requestId)->first();
            if(!empty($click)){
                echo "bai_du:更新过: {$info['open_id']} \n";
                return $info;
            }
        }else{
            $requestId = 'n8_'.md5(uniqid());
        }

        $clickAt =  isset($data['data']['ts']) && !empty($data['data']['ts'])
            ? date('Y-m-d H:i:s',intval($data['data']['ts']/1000))
            : $info['action_time'];

        $extends = $data['data'];
        $extends['match_data_id'] = $data['id'];
        $this->saveClickDataService
            ->saveAdvClickData(AdvAliasEnum::BAI_DU,[
                'ip'           => $data['data']['ip'],
                'ua'           => $data['data']['ua'],
                'click_at'     => $clickAt,
                'type'         => UserActionTypeEnum::REG,
                'product_id'   => $data['product_id'],
                'request_id'   => $requestId,
                'extends'      => $extends,
            ]);

        $info->request_id = $requestId;
        if(!empty($cpChannelId)){
            $info->cp_channel_id = $cpChannelId;
        }
        $info->save();
        echo "bai_du:更新成功: {$info['open_id']} \n";
        return $info;
    }




    /**
     * @param $productId
     * @param $openId
     * @return mixed
     * 获取用户注册记录(最近15天)
     */
    public function getRegLogInfo($productId,$openId){

        $info = $this->userActionLogModel
            ->setTableNameWithMonth($this->dateRange['end'])
            ->where('type',UserActionTypeEnum::REG)
            ->where('product_id',$productId)
            ->where('open_id',$openId)
            ->whereBetween('action_time',[
                $this->dateRange['start']. ' 00:00:00',
                $this->dateRange['end']. ' 23:59:59',
            ])
            ->first();


        if(empty($info)
            && date('Y-m',strtotime($this->dateRange['start'])) != date('Y-m',strtotime($this->dateRange['end']))
        ){

            $info = $this->userActionLogModel
                ->setTableNameWithMonth($this->dateRange['start'])
                ->where('type',UserActionTypeEnum::REG)
                ->where('product_id',$productId)
                ->where('open_id',$openId)
                ->whereBetween('action_time',[
                    $this->dateRange['start']. ' 00:00:00',
                    $this->dateRange['end']. ' 23:59:59',
                ])
                ->first();
        }

        return $info;
    }

}
