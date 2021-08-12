<?php

namespace App\Services\QyH5;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Qy\QySdk;
use App\Services\PullUserActionBaseService;


class UserFollowActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::FOLLOW;

    protected $source = DataSourceEnums::CP;

    /**
     * @var QySdk
     */
    protected $qySdk;


    public function setSdk(){
        $this->qySdk = new QySdk($this->product['cp_secret']);
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
                $tmp = $this->qySdk->getUsers($date,$page);
                $data = array_merge($data,$tmp['data']);
                $page += 1;

            }while($page <= $tmp['last_page']);
        }

        return $data;
    }


    public function pullItem($item)
    {
        if($item['is_subscribe'] == 1){
            $this->save([
                'product_id'    => $this->product['id'],
                'open_id'       => $item['openid'],
                'action_time'   => date('Y-m-d H:i:s',$item['subscribe_time']),
                'cp_channel_id' => $item['follow_referral_id'],
                'request_id'    => '',
                'ip'            => $item['register_ip'],
                'action_id'     => $item['openid'],
                'matcher'       => $this->product['matcher'],
                'extend'        => array_merge([
                    'id'      => $item['id']
                ],$this->filterExtendInfo($item)),
            ],$item);
        }
    }




}
