<?php

namespace App\Services\TwApp;



use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\TwApp\TwAppSdk;
use App\Services\PullUserActionBaseService;


class UserRegActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::REG;

    protected $source = DataSourceEnums::CP;


    public function pullPrepare(){

        $sdk = new TwAppSdk($this->product['cp_product_alias'],$this->product['cp_secret']);

        $data =  $sdk->getUsers([
            'start_date'  => $this->startTime,
            'end_date'  => $this->endTime,
        ]);

        return $data['data'];
    }


    public function pullItem($item){

        $this->save([
            'open_id'       => $item['user_id'],
            'action_time'   => $item['reg_time'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => $item['ip'],
            'action_id'     => $item['user_id'],
            'matcher'       => $this->product['matcher'],
            'extend'        => $this->filterExtendInfo($item)
        ],$item);
    }
}
