<?php

namespace App\Services\BmKyy;


use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Bm\BmSdk;
use App\Services\PullUserActionBaseService;


class UserAddShortcutActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::ADD_SHORTCUT;

    protected $source = DataSourceEnums::CP;


    public function pullPrepare(){
        $sdk = new BmSdk($this->product['cp_product_alias'],$this->product['cp_secret']);

        $data = [];
        $page = 1;
        do{
            $tmp =  $sdk->getInstallUsers($this->startTime, $this->endTime, $page);

            $data = array_merge($data,$tmp['list']);
            $page += 1;

        }while($page <= $tmp['totalPage']);


        return $data;
    }


    public function pullItem($item){

        // 加桌
        if($item['isInstall'] == 1){
            $this->save([
                'product_id'    => $this->product['id'],
                'open_id'       => $item['uuid'],
                'action_time'   => date('Y-m-d H:i:s',$item['installTime']),
                'cp_channel_id' => $item['channelid'],
                'request_id'    => '',
                'ip'            => $item['regIp'] ?? '',
                'action_id'     => $item['uuid'],
                'matcher'       => $this->product['matcher'],
                'extend'        => $this->filterExtendInfo($item),
            ],$item);
        }
    }
}
