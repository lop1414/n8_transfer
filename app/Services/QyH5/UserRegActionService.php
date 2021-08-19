<?php

namespace App\Services\QyH5;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Qy\QySdk;
use App\Services\PullUserActionBaseService;


class UserRegActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::REG;

    protected $source = DataSourceEnums::CP;

    protected $userFollowActionService;

    /**
     * @var QySdk
     */
    protected $qySdk;

    public function __construct(){
        parent::__construct();
        $this->userFollowActionService = new UserFollowActionService();
    }


    public function setSdk(){
        $this->qySdk = new QySdk($this->product['cp_secret']);
        $this->userFollowActionService->setProduct($this->product);
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
            $this->userFollowActionService->pullItem($item);
        }

        $this->updateSave([
            'product_id'    => $this->product['id'],
            'open_id'       => $item['openid'],
            'action_time'   => date('Y-m-d H:i:s',$item['createtime']),
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
