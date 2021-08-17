<?php

namespace App\Services\TwKyy;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Tw\TwSdk;
use App\Services\PullUserActionBaseService;


class UserCompleteOrderActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::COMPLETE_ORDER;

    protected $source = DataSourceEnums::CP;


    public function pullPrepare(){

        $sdk = new TwSdk($this->product['cp_product_alias'],$this->product['cp_secret']);

        $date = date('Y-m-d H:i',strtotime($this->startTime));
        $endTime = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($this->endTime)));
        $data = [];
        do{
            echo $date. "\n";
            $tmp =  $sdk->getOrders([
                'pay_time'  => $date,
                'is_pay'    => 1
            ]);
            $data = array_merge($data,$tmp);
            $date = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($date)));

        }while($date <= $endTime);
        return $data;
    }



    public function pullItem($item){
        $this->save([
            'product_id'    => $this->product['id'],
            'open_id'       => $item['uid'],
            'action_time'   => $item['finished_at'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['id'],
            'matcher'       => $this->product['matcher'],
            'extend'        => array_merge([
                'order_id'      => $item['id']
            ],$this->filterExtendInfo($item)),
        ],$item);

    }




}
