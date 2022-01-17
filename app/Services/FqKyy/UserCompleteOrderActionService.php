<?php

namespace App\Services\FqKyy;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Fq\FqSdk;
use App\Services\PullUserActionBaseService;


class UserCompleteOrderActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::COMPLETE_ORDER;

    protected $source = DataSourceEnums::CP;


    public function pullPrepare(){

        $sdk = new FqSdk($this->product['cp_product_alias'],$this->product['cp_secret']);

        echo "{$this->startTime} ~ {$this->endTime}\n";
        $offset = 0;
        $data = [];
        do{
            $list =  $sdk->getOrders($this->startTime,$this->endTime,$offset);
            $data = array_merge($list['result'],$data);
            $offset = $list['next_offset'];

        }while($list['has_more']);
        return $data;
    }



    public function pullItem($item){
        if($item['status'] == 0){
            $this->save([
                'product_id'    => $this->product['id'],
                'open_id'       => $item['device_id'],
                'action_time'   => $item['pay_time'],
                'cp_channel_id' => $item['promotion_id'],
                'request_id'    => '',
                'ip'            => '',
                'action_id'     => $item['trade_no'],
                'matcher'       => $this->product['matcher'],
                'extend'        => array_merge([
                    'order_id'      => $item['trade_no']
                ],$this->filterExtendInfo($item)),
            ],$item);
        }
    }

}
