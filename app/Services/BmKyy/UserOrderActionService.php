<?php

namespace App\Services\BmKyy;


use App\Common\Enums\OrderTypeEnums;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Bm\BmSdk;
use App\Services\PullUserActionBaseService;


class UserOrderActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ORDER;

    protected $source = DataSourceEnums::CP;

    protected $completeOrderService;

    protected $orderTypeMap = [
        1   => OrderTypeEnums::ANNUAL,
        2   => OrderTypeEnums::NORMAL,
        3   => OrderTypeEnums::NORMAL,
        4   => OrderTypeEnums::ACTIVITY
    ];


    public function __construct(){
        parent::__construct();
        $this->completeOrderService = new UserCompleteOrderActionService();
    }


    public function pullPrepare(){
        $sdk = new BmSdk($this->product['cp_product_alias'],$this->product['cp_secret']);
        $this->completeOrderService->setProduct($this->product);

        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getOrders($this->startTime, $this->endTime, $page);

            $data = array_merge($data,$tmp['list']);
            $page += 1;

        }while($page <= $tmp['totalPage']);

        return $data;
    }


    public function pullItem($item)
    {

        if($item['orderStatus'] == 1){
            $this->completeOrderService->pullItem($item);
        }

        $this->save([
            'product_id'    => $this->product['id'],
            'open_id'       => $item['uuid'],
            'action_time'   => date('Y-m-d H:i:s',$item['createTime']),
            'cp_channel_id' => $item['channelid'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['orderSn'],
            'matcher'       => $this->product['matcher'],
            'extend'        => array_merge([
                'amount'        => $item['orderPrice'],
                'type'          => $this->orderTypeMap[$item['orderType']],
                'order_id'      => $item['orderSn']
            ],$this->filterExtendInfo($item)),
        ],$item);
    }


}
