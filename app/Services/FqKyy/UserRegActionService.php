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
        $offset = 0;
        $data = [];
        do{
            $list =  $sdk->getUsers($this->startTime,$this->endTime,$offset);
            foreach ($list['result'] as $item ){

                $tmp = $sdk->readAdInfo($item['device_id']);


                $userAdInfo = empty($tmp['result']) ? [] :$tmp['result'][0];

                $item['ad_info'] = $userAdInfo;
                $data[] = $item ;
            }
            $offset = $list['next_offset'];

        }while($list['has_more']);
        return $data;
    }


    public function pullItem($item){

        $userAdInfo = $item['ad_info'] ?: ['ip' => '','user_agent' => '','clickid'  => '','oaid' => '','caid' => ''];
        $item['oaid'] = $item['oaid'] ?: $userAdInfo['oaid'];
        $item['caid'] = $item['caid'] ?: $userAdInfo['caid'];
        $this->save([
            'open_id'       => $item['device_id'],
            'action_id'     => $item['device_id'],
            'action_time'   => date('Y-m-d H:i:s',$item['register_timestamp']),
            'cp_channel_id' => $item['promotion_id'],
            'matcher'       => $this->product['matcher'],
            'ip'            => $userAdInfo['ip'],
            'ua'            => $userAdInfo['user_agent'],
            'adv_click_id'  => $userAdInfo['clickid'],
            'extend'        => $this->filterExtendInfo($item),
            'request_id'    => ''
        ],$item);

    }

}
