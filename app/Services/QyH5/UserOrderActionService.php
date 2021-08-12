<?php

namespace App\Services\QyH5;


use App\Common\Enums\OrderTypeEnums;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Qy\QySdk;
use App\Services\PullUserActionBaseService;


class UserOrderActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ORDER;

    protected $source = DataSourceEnums::CP;

    protected $completeOrderService;

    /**
     * @var QySdk
     */
    protected $qySdk;

    protected $orderTypeMap = [
        1   => OrderTypeEnums::NORMAL,
        2   => OrderTypeEnums::PROP
    ];


    public function __construct(){
        parent::__construct();
        $this->completeOrderService = new UserCompleteOrderActionService();
    }


    public function setSdk(){
        $this->qySdk = new QySdk($this->product['cp_secret']);
        $this->completeOrderService->setProduct($this->product);
    }


    public function pullPrepare(){
        $this->setSdk();
        $dates = array_unique([
            date('Y-m-d',strtotime($this->startTime)),
            date('Y-m-d',strtotime($this->endTime))
        ]);
        $data = [];

        foreach ($dates as $date){
            $page = 1;

            do{
                $tmp = $this->qySdk->getOrders($date,$page);
                $data = array_merge($data,$tmp['data']);
                $page += 1;

            }while($page <= $tmp['last_page']);
        }

        return $data;
    }


    public function pullItem($item)
    {
        if($item['state'] == 2){
            $this->completeOrderService->pullItem($item);
        }

        $channelId = '';
        if(!empty($item['referral_url'])){
            $ret = parse_url($item['referral_url']);
            parse_str($ret['query'], $urlParam);
            $channelId = $urlParam['referral_id'] ?? '';
        }
        $this->save([
            'product_id'    => $this->product['id'],
            'open_id'       => $item['user_open_id'],
            'action_time'   => date('Y-m-d H:i:s',$item['create_time']),
            'cp_channel_id' => $channelId,
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['trade_no'],
            'matcher'       => $this->product['matcher'],
            'extend'        => array_merge([
                'amount'        => $item['money'] * 100,
                'type'          => $this->orderTypeMap[$item['type']],
                'order_id'      => $item['trade_no']
            ],$this->filterExtendInfo($item)),
        ],$item);


    }


}
