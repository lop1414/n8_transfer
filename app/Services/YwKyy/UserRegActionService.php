<?php

namespace App\Services\YwKyy;


use App\Enums\UserActionTypeEnum;
use App\Services\UserActionBaseService;


class UserRegActionService extends UserActionBaseService
{

    protected $actionType = UserActionTypeEnum::REG;


    public function pushItemPrepare($item){
        $rawData = $item['data'];
        return [
            'product_alias' => $this->product['cp_product_alias'],
            'cp_type'       => $this->product['cp_type'],
            'open_id'       => $item['open_id'],
            'action_time'   => $item['action_time'],
            'cp_channel_id' => $item['cp_channel_id'],
            'ip'            => $item['ip'],
            'ua'            => $rawData['user_info']['ua'] ?? '',
            'muid'          => $rawData['user_info']['muid'] ?? '',
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
