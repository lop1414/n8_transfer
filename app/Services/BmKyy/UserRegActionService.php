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
        $startTime = strtotime($this->startTime);
        $endTime = strtotime($this->endTime);

        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getUsers($startTime, $endTime, $page);

            $data = array_merge($data,$tmp['list']);
            $page += 1;

        }while($page <= $tmp['totalPage']);


        return $data;
    }


    public function pullItem($item){

        $requestId = 'n8_'.md5(uniqid());

        $adv = $this->getAdv($item['platform']);
        $ip = long2ip($item['clientIp']);

        //有匹配到的计划
        if(!empty($item['externalPlanid'])){

            $clickData = [
                'ip'           => $ip,
                'muid'         => '',
                'oaid'         => $item['oaid'] ?? '',
                'os'           => $advData['os'] ?? '',
                'click_at'     => $item['reg_time'],
                'ad_id'        => $advData['adid'] ?? '',
                'creative_id'  => $advData['cid'] ?? '',
                'union_site'   => '',
                'request_id'   => $requestId,
                'type'         => $this->actionType
            ];

            $adv = $adv ?: AdvAliasEnum::OCEAN;
            $this->saveAdvClickData($adv,$clickData);
        }

        $this->save([
            'open_id'       => $item['id'],
            'action_time'   => date('Y-m-d H:i:s',$item['regTime']),
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => $requestId,
            'ip'            => $ip,
            'action_id'     => $item['id'],
            'matcher'       => $this->product['matcher'],
            'extend'        => $this->filterExtendInfo($item)
        ],$item);


        if($item['is_save_shortcuts'] == 1){
            $this->userAddShortcutActionService->pullItem($item);
        }

    }





}
