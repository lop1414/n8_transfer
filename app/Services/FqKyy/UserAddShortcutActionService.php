<?php

namespace App\Services\FqKyy;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Fq\FqSdk;
use App\Services\PullUserActionBaseService;


class UserAddShortcutActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ADD_SHORTCUT;

    protected $source = DataSourceEnums::CP;


    public function pullPrepare(){

        $sdk = new FqSdk($this->product['cp_product_alias'],$this->product['cp_secret']);
        echo "{$this->startTime} ~ {$this->endTime}\n";
        $offset = 0;
        $data = [];
//        do{
//            $list =  $sdk->getAddDesktopActions($this->startTime,$this->endTime,$offset);
//            $data = array_merge($list['result'],$data);
//            $offset = $list['next_offset'];
//
//        }while($list['has_more']);
        return $data;
    }


    public function pullItem($item){
        $saveData = [
            'product_id'    => $this->product['id'],
            'open_id'       => $item['encrypted_device_id'],
            'action_time'   => date('Y-m-d H:i:s',$item['timestamp']),
            'cp_channel_id' => $item['promotion_id'],
            'request_id'    => '',
            'ip'            => $item['ip'],
            'action_id'     => $item['encrypted_device_id'],
            'matcher'       => $this->product['matcher'],
            'extend'        => $this->filterExtendInfo($item),
        ];

        // 重复加桌
        if($this->isRepeatAddShortcut($saveData)){
            return;
        }

        $this->save($saveData,$item);

    }
}
