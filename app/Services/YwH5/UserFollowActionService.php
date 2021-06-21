<?php

namespace App\Services\YwH5;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Yw\YwSdk;
use App\Services\ProductService;
use App\Services\PullUserActionBaseService;


class UserFollowActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::FOLLOW;

    protected $source = DataSourceEnums::CP;


    protected $ywSdk;


    public function setYwSdk(){
        if(empty($this->ywSdk)){
            $cpAccount = (new ProductService())->readCpAccount($this->product['cp_account_id']);
            $this->ywSdk = new YwSdk($this->product['cp_product_alias'],$cpAccount['account'],$cpAccount['cp_secret']);

        }
    }


    public function pullPrepare(){
        $this->setYwSdk();
        $reqPara = [
            'start_time'  => strtotime($this->startTime),
            'end_time'  => strtotime($this->endTime),
            'page'   => 1
        ];

        $data = [];
        do{

            $tmp = $this->ywSdk->getWxUser($reqPara);

            $data = array_merge($data,$tmp['list']);
            $reqPara['page'] += 1;
            $reqPara['next_id'] = $tmp['next_id'];

        }while(count($data) < $tmp['total_count']);

        return $data;
    }


    public function pullItem($item)
    {
        if($item['is_subscribe'] == 1){
            $this->save([
                'product_id'    => $this->product['id'],
                'open_id'       => $item['openid'],
                'action_time'   => $item['create_time'],
                'cp_channel_id' => $item['channel_id'],
                'request_id'    => '',
                'ip'            => '',
                'action_id'     => $item['openid'],
                'matcher'       => $this->product['matcher'],
                'extend'        => array_merge([
                    'guid'      => $item['guid']
                ],$this->filterExtendInfo($item)),
            ],$item);
        }
    }




}
