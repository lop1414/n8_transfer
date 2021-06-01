<?php

namespace App\Services\SecondVersionYwKyy;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\SecondVersion\SecondVersionSdk;
use App\Services\UserActionBaseService;


class UserAddShortcutActionService extends UserActionBaseService
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
            'matcher'       => $this->product['matcher']
        ],$item);
    }









    public function pushItemPrepare($item){
        $rawData = $item['data'];

        return [
            'product_alias' => $this->product['cp_product_alias'],
            'cp_type'       => $this->product['cp_type'],
            'open_id'       => $item['open_id'],
            'action_time'   => $item['action_time'],
            'cp_channel_id' => $item['cp_channel_id'],
            'ip'            => $item['ip'],
            'ua'            => $rawData['extend']['ua'] ? base64_decode($rawData['extend']['ua']) : '' ,
            'muid'          => $rawData['extend']['muid'] ?? '',
            'device_brand'          => '',
            'device_manufacturer'   => '',
            'device_model'          => '',
            'device_product'        => '',
            'device_os_version_name'    => '',
            'device_os_version_code'    => '',
            'device_platform_version_name'  => '',
            'device_platform_version_code'  => '',
            'android_id'            => $rawData['extend']['android_id'] ?? '',
            'request_id'            => $item['request_id']
        ];
    }


}
