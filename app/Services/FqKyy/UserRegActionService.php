<?php

namespace App\Services\FqKyy;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Fq\FqSdk;
use App\Services\PullUserActionBaseService;


class UserRegActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::REG;

    protected $source = DataSourceEnums::CP;


    public function pullPrepare(){

        $sdk = new FqSdk($this->product['cp_product_alias'],$this->product['cp_secret']);

        echo "{$this->startTime} ~ {$this->endTime}\n";
        $page = 0;
        $data = [];
        $pageSize = 100;
        do{
            $list =  $sdk->getUserList($this->startTime,$this->endTime,$page,$pageSize);
            $data = array_merge($list['data'],$data);
            $page += 1;
            $number = count($data);
        }while($number < $list['total']);
        return $data;
    }


    public function pullItem($item){
        $this->save([
            'open_id'       => $item['encrypted_device_id'],
            'action_id'     => $item['encrypted_device_id'],
            'action_time'   => $item['register_time'],
            'cp_channel_id' => $item['promotion_id'],
            'matcher'       => $this->product['matcher'],
            'ip'            => $item['ip'] ?? '',
            'ua'            => $item['user_agent'] ?? '',
            'adv_click_id'  => $item['clickid'],
            'extend'        => $this->filterExtendInfo($item),
            'request_id'    => ''
        ],$item);
        if($item['timestamp'] > 0){
            (new UserAddShortcutActionService())->setProduct($this->product)->pullItem($item);
        }

    }

}
