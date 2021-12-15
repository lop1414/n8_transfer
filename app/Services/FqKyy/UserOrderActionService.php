<?php

namespace App\Services\FqKyy;


use App\Common\Enums\OrderTypeEnums;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Fq\FqSdk;
use App\Services\PullUserActionBaseService;


class UserOrderActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ORDER;

    protected $source = DataSourceEnums::CP;


    protected $completeOrderService;


    public function __construct(){
        parent::__construct();
        $this->completeOrderService = new UserCompleteOrderActionService();
    }


    public function pullPrepare(){

        $sdk = new FqSdk($this->product['cp_product_alias'],$this->product['cp_secret']);
        $this->completeOrderService->setProduct($this->product);

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
        $item['status']  = 0;
        $item['pay_time'] = date('Y-m-d H:i:s');
        if($item['status'] == 0){
            $this->completeOrderService->pullItem($item);
        }

        $this->save([
            'product_id'    => $this->product['id'],
            'open_id'       => $item['device_id'],
            'action_time'   => $item['create_time'],
            'cp_channel_id' => $item['promotion_id'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['trade_no'],
            'matcher'       => $this->product['matcher'],
            'extend'        => array_merge([
                'amount'        => $item['pay_fee'],
                'type'          => $item['is_activity']? OrderTypeEnums::ACTIVITY : OrderTypeEnums::ANNUAL,
                'order_id'      => $item['trade_no']
            ],$this->filterExtendInfo($item)),
        ],$item);

    }




}
