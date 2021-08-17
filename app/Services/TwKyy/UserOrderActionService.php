<?php

namespace App\Services\TwKyy;


use App\Common\Enums\CpTypeEnums;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Models\ConfigModel;
use App\Sdks\Tw\TwSdk;
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

        $sdk = new TwSdk($this->product['cp_product_alias'],$this->product['cp_secret']);
        $this->completeOrderService->setProduct($this->product);

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

        if($item['is_pay'] == 1){
            $this->completeOrderService->pullItem($item);
        }

        $orderTypeMap = $this->getOrderTypeMap();

        $this->save([
            'product_id'    => $this->product['id'],
            'open_id'       => $item['uid'],
            'action_time'   => $item['created_at'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['id'],
            'matcher'       => $this->product['matcher'],
            'extend'        => array_merge([
                'amount'        => $item['amount'],
                'type'          => $orderTypeMap[$item['type']],
                'order_id'      => $item['id']
            ],$this->filterExtendInfo($item)),
        ],$item);

    }




}
