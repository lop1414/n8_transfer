<?php

namespace App\Services\TwApp;


use App\Common\Enums\CpTypeEnums;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Models\ConfigModel;
use App\Sdks\Tw\TwSdk;
use App\Sdks\TwApp\TwAppSdk;
use App\Services\PullUserActionBaseService;


class UserOrderActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ORDER;

    protected $source = DataSourceEnums::CP;

    protected $orderTypeMap;

    protected $completeOrderService;


    public function __construct(){
        parent::__construct();
        $this->completeOrderService = new UserCompleteOrderActionService();
    }


    public function getOrderTypeMap(){
        if(empty($this->orderTypeMap)){
            $this->orderTypeMap = (new ConfigModel())
                ->where('group',CpTypeEnums::TW)
                ->where('k','order_type_map')
                ->first()
                ->v;
        }
        return  $this->orderTypeMap;
    }

    public function pullPrepare(){

        $sdk = new TwAppSdk($this->product['cp_product_alias'],$this->product['cp_secret']);
        $this->completeOrderService->setProduct($this->product);

        $data  =  $sdk->getOrders([
            'start_create_date'  => $this->startTime,
            'end_create_date'  => $this->endTime,
        ]);

        return $data['data'];
    }


    public function pullItem($item){

        if($item['is_pay'] == 1){
            $this->completeOrderService->pullItem($item);
        }

        $orderTypeMap = $this->getOrderTypeMap();

        $this->save([
            'product_id'    => $this->product['id'],
            'open_id'       => $item['user_id'],
            'action_time'   => $item['created_at'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['order_id'],
            'matcher'       => $this->product['matcher'],
            'extend'        => array_merge([
                'amount'        => $item['amount'] * 100,
                'type'          => $orderTypeMap[$item['type']],
                'order_id'      => $item['order_id']
            ],$this->filterExtendInfo($item)),
        ],$item);

    }




}
