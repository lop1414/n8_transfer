<?php

namespace App\Services\BmKyy;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Bm\BmSdk;
use App\Services\PullUserActionBaseService;


class UserCompleteOrderActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::COMPLETE_ORDER;

    protected $source = DataSourceEnums::CP;


    public function pullPrepare(){
        $sdk = new BmSdk($this->product['cp_product_alias'],$this->product['cp_secret']);

        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getPayOrders($this->startTime, $this->endTime, $page);

            $data = array_merge($data,$tmp['list']);
            $page += 1;

        }while($page <= $tmp['totalPage']);

        return $data;
    }


    public function pullItem($item)
    {

        if($item['orderStatus'] == 1){
            $this->save([
                'product_id'    => $this->product['id'],
                'open_id'       => $item['uuid'],
                'action_time'   => date('Y-m-d H:i:s',$item['payTime']),
                'cp_channel_id' => $item['channelid'],
                'request_id'    => '',
                'ip'            => '',
                'action_id'     => $item['orderSn'],
                'matcher'       => $this->product['matcher'],
                'extend'        => array_merge([
                    'order_id'      => $item['orderSn']
                ],$this->filterExtendInfo($item)),
            ],$item);
        }
    }


}
