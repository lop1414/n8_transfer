<?php

namespace App\Services\TwApp;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\TwApp\TwAppSdk;
use App\Services\PullUserActionBaseService;


class UserCompleteOrderActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::COMPLETE_ORDER;

    protected $source = DataSourceEnums::CP;


    public function pullPrepare(){

        $sdk = new TwAppSdk($this->product['cp_product_alias'],$this->product['cp_secret']);

        $data  =  $sdk->getOrders([
            'is_pay'            => 1,
            'start_create_date' => $this->startTime,
            'end_create_date'   => $this->endTime,
        ]);

        return $data['data'];
    }



    public function pullItem($item){
        $this->save([
            'product_id'    => $this->product['id'],
            'open_id'       => $item['user_id'],
            'action_time'   => $item['finished_at'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['order_id'],
            'matcher'       => $this->product['matcher'],
            'extend'        => array_merge([
                'order_id'      => $item['order_id']
            ],$this->filterExtendInfo($item)),
        ],$item);

    }
}
