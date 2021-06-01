<?php

namespace App\Services\YwKyy;


use App\Common\Enums\OrderTypeEnums;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Yw\YwSdk;
use App\Services\ProductService;
use App\Services\UserActionBaseService;


class UserOrderActionService extends UserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ORDER;

    protected $source = DataSourceEnums::CP;

    protected $ywSdk;

    protected $orderTypeMap = [
        1   => OrderTypeEnums::NORMAL,
        2   => OrderTypeEnums::ANNUAL,
        3   => OrderTypeEnums::PROP
    ];


    public function setYwSdk(){
        if(empty($this->ywSdk)){
            $cpAccount = (new ProductService())->readCpAccount($this->product['cp_account_id']);
            $this->ywSdk = new YwSdk($this->product['cp_product_alias'],$cpAccount['account'],$cpAccount['cp_secret']);
        }
    }


    public function pullPrepare(){
        $this->setYwSdk();
        $reqPara = [
            'coop_type'  => 11,
            'start_time'  => strtotime($this->startTime),
            'end_time'  => strtotime($this->endTime),
            'page'   => 1
        ];

        $data = [];
        do{

            $tmp = $this->ywSdk->getOrders($reqPara);
            $data = array_merge($data,$tmp['list']);
            $reqPara['page'] += 1;
            $reqPara['last_min_id'] = $tmp['min_id'];
            $reqPara['last_max_id'] = $tmp['max_id'];
            $reqPara['total_count'] = $tmp['total_count'];
            $reqPara['last_page'] = $tmp['page'];

        }while(count($data) < $tmp['total_count']);

        return $data;
    }


    public function pullItem($item)
    {

        $this->save([
            'open_id'       => $item['guid'],
            'action_time'   => $item['order_time'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['yworder_id'],
            'matcher'       => $this->product['matcher']
        ],$item);
    }


    public function pushItemPrepare($item){
        $rawData = $item['data'];
        return [
            'product_alias' => $this->product['cp_product_alias'],
            'cp_type'       => $this->product['cp_type'],
            'open_id'       => $rawData['guid'],
            'order_id'      => $rawData['yworder_id'],
            'action_time'   => $rawData['order_time'],
            'cp_channel_id' => $rawData['channel_id'],
            'amount'        => $rawData['amount'] * 100,
            'type'          => $this->orderTypeMap[$rawData['order_type']],
            'ip'            => '',
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
            'request_id'            => ''
        ];
    }


}
