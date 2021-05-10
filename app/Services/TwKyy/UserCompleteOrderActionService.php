<?php

namespace App\Services\TwKyy;


use App\Enums\UserActionTypeEnum;
use App\Sdks\Tw\TwSdk;
use App\Services\UserActionBaseService;


class UserCompleteOrderActionService extends UserActionBaseService
{

    protected $actionType = UserActionTypeEnum::COMPLETE_ORDER;



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
            'open_id'       => $item['uid'],
            'action_time'   => $item['finished_at'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['id'],
            'matcher'       => $this->product['matcher']
        ],$item);

    }










    public function pushItemPrepare($item){
        return [
            'product_alias' => $this->product['cp_product_alias'],
            'cp_type'       => $this->product['cp_type'],
            'order_id'      => $item['id'],
            'complete_time'   => $item['action_time']
        ];
    }



}
