<?php

namespace App\Services\TwKyy;


use App\Common\Enums\CpTypeEnums;
use App\Enums\UserActionTypeEnum;
use App\Models\ConfigModel;
use App\Sdks\Tw\TwSdk;
use App\Services\UserActionBaseService;


class UserOrderActionService extends UserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ORDER;


    protected $orderTypeMap;


    public function setOrderTypeMap(){
        $this->orderTypeMap = (new ConfigModel())
            ->where('group',CpTypeEnums::TW)
            ->where('k','order_type_map')
            ->first()
            ->v;
    }

    public function pullPrepare(){



        $sdk = new TwSdk($this->product['cp_product_alias'],$this->product['cp_secret']);

        $date = date('Y-m-d H:i',strtotime($this->startTime));
        $endTime = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($this->endTime)));
        $data = [];
        do{
            echo $date. "\n";
            $tmp =  $sdk->getOrders([
                'pay_time'  => $date
            ]);
            $data = array_merge($data,$tmp);
            $date = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($date)));

        }while($date <= $endTime);
        return $data;
    }


    public function pullItem($item){

        $this->save([
            'open_id'       => $item['uid'],
            'action_time'   => $item['created_at'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['id'],
            'matcher'       => $this->product['matcher']
        ],$item);

    }







    public function pushPrepare(){
        $this->setOrderTypeMap();
    }


    public function pushItemPrepare($item){
        $rawData = $item['data'];
        return [
            'product_alias' => $this->product['cp_product_alias'],
            'cp_type'       => $this->product['cp_type'],
            'open_id'       => $item['open_id'],
            'order_id'      => $item['id'],
            'action_time'   => $item['action_time'],
            'cp_channel_id' => $item['cp_channel_id'],
            'amount'        => $rawData['amount'],
            'type'          => $this->orderTypeMap[$rawData['type']],
            'ip'            => '',
            'ua'            => '',
            'muid'          => $rawData['imei'],
            'oaid'          => $rawData['oaid'],
            'device_brand'          => '',
            'device_manufacturer'   => '',
            'device_model'          => '',
            'device_product'        => $rawData['device_product'],
            'device_os_version_name'    => '',
            'device_os_version_code'    => '',
            'device_platform_version_name'  => '',
            'device_platform_version_code'  => '',
            'android_id'            => '',
            'request_id'            => $item['request_id']
        ];
    }





}
