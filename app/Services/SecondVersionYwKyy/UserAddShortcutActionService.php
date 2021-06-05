<?php

namespace App\Services\SecondVersionYwKyy;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\SecondVersion\SecondVersionSdk;
use App\Services\PullUserActionBaseService;


class UserAddShortcutActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ADD_SHORTCUT;


    protected $source = DataSourceEnums::SECOND_VERSION;


    public function pullPrepare(){
        $sdk = new SecondVersionSdk();
        return $sdk->getUserAddShortcutAction($this->product['cp_product_alias'],$this->product['cp_type'],$this->startTime,$this->endTime);
    }



    public function pullItem($item){
        $rawData = $item['extend'];
        $cpChannelId = $item['valid_info']['custom_alias'] ?? 0;
        $this->save([
            'open_id'       => $rawData['guid'],
            'action_time'   => date('Y-m-d H:i:s',$rawData['time']),
            'cp_channel_id' => $cpChannelId ?: 0,
            'request_id'    => '',
            'ip'            => $rawData['ip'],
            'action_id'     => $rawData['guid'],
            'matcher'       => $this->product['matcher'],
            'extend'        => $this->filterExtendInfo([
                'ua'            => $rawData['extend']['ua'] ? base64_decode($rawData['extend']['ua']) : '',
                'muid'          => $rawData['user_info']['muid'] ?? '',
                'android_id'    => $rawData['extend']['android_id'] ?? '',
            ])
        ],$item);
    }


}
