<?php

namespace App\Services\YwKyy;


use App\Common\Enums\OrderTypeEnums;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Models\UserActionLogModel;
use App\Sdks\Yw\YwSdk;
use App\Services\ProductService;
use App\Services\PullUserActionBaseService;


class UserOrderActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ORDER;

    protected $source = DataSourceEnums::CP;

    protected $completeOrderService;

    protected $ywSdk;

    protected $orderTypeMap = [
        1   => OrderTypeEnums::NORMAL,
        2   => OrderTypeEnums::ANNUAL,
        3   => OrderTypeEnums::PROP
    ];


    public function __construct(){
        parent::__construct();
        $this->completeOrderService = new UserCompleteOrderActionService();
    }


    public function setYwSdk(){
        if(empty($this->ywSdk)){
            $cpAccount = (new ProductService())->readCpAccount($this->product['cp_account_id']);
            $this->ywSdk = new YwSdk($this->product['cp_product_alias'],$cpAccount['account'],$cpAccount['cp_secret']);

            $this->completeOrderService->setProduct($this->product);
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
            'product_id'    => $this->product['id'],
            'open_id'       => $item['guid'],
            'action_time'   => $item['order_time'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['yworder_id'],
            'matcher'       => $this->product['matcher'],
            'extend'        => array_merge([
                'amount'        => $item['amount'] * 100,
                'type'          => $this->orderTypeMap[$item['order_type']],
                'order_id'      => $item['yworder_id']
            ],$this->filterExtendInfo($item)),
        ],$item);

        if($item['order_status'] == 2){
            $this->completeOrderService->pullItem($item);
        }
    }


    public function check(){
        $this->setYwSdk();
        $reqPara = [
            'coop_type'  => 11,
            'start_time'  => strtotime($this->startTime),
            'end_time'  => strtotime($this->endTime)
        ];
        $tmp = $this->ywSdk->getOrders($reqPara);
        $total =  $tmp['total_count'];

        $dbCount = (new UserActionLogModel())
            ->setTableNameWithMonth($this->startTime)
            ->whereBetween('action_time',[$this->startTime,$this->endTime])
            ->where('product_id',$this->product['id'])
            ->where('type',$this->actionType)
            ->count();

        if($total != $dbCount){
            $diff = $total - $dbCount;
            echo " 相差{$diff} \n";
            $this->pull();
        }

    }


}
