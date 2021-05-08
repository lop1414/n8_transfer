<?php

namespace App\Services\TwKyy;


use App\Common\Enums\CpTypeEnums;
use App\Enums\UserActionTypeEnum;
use App\Models\ConfigModel;
use App\Sdks\Tw\TwSdk;
use App\Services\UserActionBaseService;


class UserAddShortcutActionService extends UserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ADD_SHORTCUT;


    public function pullPrepare(){

        $sdk = new TwSdk($this->product['cp_product_alias'],$this->product['cp_secret']);

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

        // 未加桌
        if($item['is_save_shortcuts'] != 1){
            return;
        }



        $this->save([
            'open_id'       => $item['id'],
            'action_time'   => $item['reg_time'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => $item['device_ip'],
            'action_id'     => $item['id'],
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
            'ua'            => '',
            'muid'          => $rawData['imei'],
            'device_brand'          => $rawData['device_company'],
            'device_manufacturer'   => $rawData['device_product'],
            'device_model'          => '',
            'device_product'        => $rawData['device_product'],
            'device_os_version_name'    => '',
            'device_os_version_code'    => $rawData['device_os'],
            'device_platform_version_name'  => '',
            'device_platform_version_code'  => '',
            'android_id'            => $rawData['android_id'],
            'request_id'            => $item['request_id']
        ];
    }





}
