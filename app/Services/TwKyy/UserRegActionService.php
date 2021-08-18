<?php

namespace App\Services\TwKyy;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\CpTypeEnums;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Models\ConfigModel;
use App\Sdks\Tw\TwSdk;
use App\Services\PullUserActionBaseService;


class UserRegActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::REG;

    protected $source = DataSourceEnums::CP;

    protected $userAddShortcutActionService;

    protected $advMap;


    public function __construct(){
        parent::__construct();
        $this->userAddShortcutActionService = new UserAddShortcutActionService();
    }


    /**
     * @param string $pt  代运营商ID
     * @return mixed|string
     * 获取腾文快应用广告商映射
     */
    public function getAdv($pt){
        if(empty($this->twKyyAdvMap)){
            $this->advMap = (new ConfigModel())
                ->where('group',CpTypeEnums::TW)
                ->where('k','adv_map')
                ->first()
                ->v;
        }

        return $this->twKyyAdvMap[$pt] ?? '';
    }



    public function pullPrepare(){

        $sdk = new TwSdk($this->product['cp_product_alias'],$this->product['cp_secret']);
        $this->userAddShortcutActionService->setProduct($this->product);
        $date = date('Y-m-d H:i',strtotime($this->startTime));
        $endTime = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($this->endTime)));
        $data = [];
        do{
            echo $date. "\n";
            $tmp =  $sdk->getUsers([
                'reg_time'  => $date
            ]);
            $data = array_merge($data,$tmp);
            $date = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($date)));

        }while($date <= $endTime);
        return $data;
    }


    public function pullItem($item){

        $requestId = $advData['request_id'] ?? '';

        $adv = $this->getAdv($item['pt']);

        //有广告商
        if(!empty($item['data'])){
            if(empty($requestId)){
                $requestId = 'n8_'.md5(uniqid());
            }

            $advData = $item['data'];

            $unionSite = '';
            if(isset($advData['union_site']) && $advData['union_site'] != '__UNION_SITE__'){
                $unionSite = $advData['union_site'];
            }
            $clickData = [
                'ip'           => $advData['ip'] ?? '',
                'muid'         => $advData['imei'] ?? '',
                'oaid'         => $advData['oaid'] ?? '',
                'os'           => $advData['os'] ?? '',
                'click_at'     => $item['reg_time'],
                'ad_id'        => $advData['adid'] ?? '',
                'creative_id'  => $advData['cid'] ?? '',
                'union_site'   => $unionSite,
                'request_id'   => $requestId,
                'type'         => $this->actionType
            ];

            $adv = $adv ?: AdvAliasEnum::OCEAN;
            $this->saveAdvClickData($adv,$clickData);
        }

        $this->save([
            'open_id'       => $item['id'],
            'action_time'   => $item['reg_time'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => $requestId,
            'ip'            => $item['device_ip'],
            'action_id'     => $item['id'],
            'matcher'       => $this->product['matcher'],
            'extend'        => $this->filterExtendInfo($item)
        ],$item);


        if($item['is_save_shortcuts'] == 1){
            $this->userAddShortcutActionService->pullItem($item);
        }

    }





}
