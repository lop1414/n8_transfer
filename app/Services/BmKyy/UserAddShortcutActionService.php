<?php

namespace App\Services\BmKyy;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Tw\TwSdk;
use App\Services\PullUserActionBaseService;


class UserAddShortcutActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ADD_SHORTCUT;

    protected $source = DataSourceEnums::CP;


    public function pullPrepare(){

        $sdk = new TwSdk($this->product['cp_product_alias'],$this->product['cp_secret']);

        $date = date('Y-m-d H:i',strtotime($this->startTime));
        $endTime = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($this->endTime)));
        $data = [];
        do{
            echo $date. "\n";
            $tmp =  $sdk->getUsers([
                'reg_time'  => $date
            ]);
            $data = array_merge($data,$tmp);
            $date = date('Y-m-d H:i',strtotime('+1 minutes',strtotime($date)));

        }while($date <= $endTime);
        return $data;
    }


    public function pullItem($item){

        // 加桌
        if($item['is_save_shortcuts'] == 1){
            $this->save([
                'product_id'    => $this->product['id'],
                'open_id'       => $item['id'],
                'action_time'   => $item['reg_time'],
                'cp_channel_id' => $item['channel_id'],
                'request_id'    => '',
                'ip'            => $item['device_ip'],
                'action_id'     => $item['id'],
                'matcher'       => $this->product['matcher'],
                'extend'        => $this->filterExtendInfo($item),
            ],$item);
        }
    }
}
