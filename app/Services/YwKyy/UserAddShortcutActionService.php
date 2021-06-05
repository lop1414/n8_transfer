<?php

namespace App\Services\YwKyy;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Services\PullUserActionBaseService;


class UserAddShortcutActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ADD_SHORTCUT;

    protected $source = DataSourceEnums::CP;


    public function pullPrepare(){
        return [];
    }

    /**
     * @param $item
     * @return array|void
     * TODO
     */
    public function pushItemPrepare($item){
        $rawData = $item['data'];

        return [
            'product_alias' => $this->product['cp_product_alias'],
            'cp_type'       => $this->product['cp_type'],
            'open_id'       => $item['open_id'],
            'action_time'   => $item['action_time'],
            'cp_channel_id' => $item['cp_channel_id'],
            'ip'            => $item['ip'],
            'ua'            => '' ,
            'muid'          => '',
            'device_brand'          => '',
            'device_manufacturer'   => '',
            'device_model'          => '',
            'device_product'        => '',
            'device_os_version_name'    => '',
            'device_os_version_code'    => '',
            'device_platform_version_name'  => '',
            'device_platform_version_code'  => '',
            'android_id'            => '',
            'request_id'            => $item['request_id']
        ];
    }


}
