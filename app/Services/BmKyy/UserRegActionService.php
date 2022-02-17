<?php

namespace App\Services\BmKyy;


use App\Common\Enums\AdvAliasEnum;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Bm\BmSdk;
use App\Services\PullUserActionBaseService;


class UserRegActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::REG;

    protected $source = DataSourceEnums::CP;

    protected $userAddShortcutActionService;

    protected $advMap = [
        0 => AdvAliasEnum::UNKNOWN,
        1 => AdvAliasEnum::OCEAN,
        2 => AdvAliasEnum::MP,
        3 => AdvAliasEnum::GDT
    ];


    public function __construct(){
        parent::__construct();
        $this->userAddShortcutActionService = new UserAddShortcutActionService();
    }


    /**
     * @param string $platform
     * @return mixed|string
     * 获取广告商
     */
    public function getAdv($platform){
        return $this->advMap[$platform] ?? '';
    }



    public function pullPrepare(){

        $sdk = new BmSdk($this->product['cp_product_alias'],$this->product['cp_secret']);
        $this->userAddShortcutActionService->setProduct($this->product);

        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getChangeChannelLog($this->startTime, $this->endTime, $page);
            $data = array_merge($data,$tmp['list']);
            $page += 1;

        }while($page <= $tmp['totalPage']);


        return $data;
    }


    public function pullItem($item){

        $requestId = 'n8_'.md5(uniqid());

        $adv = $this->getAdv($item['platform']);

        $item['ua'] = $item['regUa'] ?? '';
        $item['android_id'] = $item['androidid'] ?? '';
        $ip = $item['clientIp'] ?? '';
        if(!empty($ip)){
            $ip = $this->isIpv6($ip) ? $ip :long2ip($ip);
        }

        //有匹配到的计划
        if(!empty($item['externalPlanid'])){

            $clickData = [
                'ip'           => $ip,
                'muid'         => '',
                'oaid'         => $item['oaid'] ?? '',
                'os'           => 0,
                'click_at'     => $item['createTime'],
                'ad_id'        => $item['externalPlanid'],
                'creative_id'  => '',
                'union_site'   => '',
                'request_id'   => $requestId,
                'type'         => $this->actionType
            ];

            $adv = $adv ?: AdvAliasEnum::OCEAN;
            $this->saveAdvClickData($adv,$clickData);
        }



        $this->save([
            'open_id'       => $item['uuid'],
            'action_time'   => $item['createTime'],
            'cp_channel_id' => $item['channelid'],
            'request_id'    => $requestId,
            'ip'            => $ip,
            'action_id'     => $item['uuid'],
            'matcher'       => $this->product['matcher'],
            'extend'        => $this->filterExtendInfo($item)
        ],$item);
    }
}
