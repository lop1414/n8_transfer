<?php

namespace App\Services\TwKyy;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ReportStatusEnum;
use App\Enums\UserActionTypeEnum;
use App\Models\ConfigModel;
use App\Sdks\Tw\TwSdk;
use App\Services\PullUserActionBaseService;


class UserRegActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::REG;


    protected $advMap;


    public function setAdvMap(){
        $this->advMap = (new ConfigModel())
            ->where('group',CpTypeEnums::TW)
            ->where('k','adv_map')
            ->first()
            ->v;
    }

    public function pullPrepare(){

        $this->setAdvMap();

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

        $requestId = $advData['request_id'] ?? '';


        $adv = $this->advMap[$item['pt']] ?? '';

        //有广告商
        if(!empty($adv)){
            if(empty($requestId)){
                $requestId = 'n8_'.md5(uniqid());
            }

            $advData = $item['data'];
            $clickData = [
                'ip'           => $advData['ip'] ?? '',
                'muid'         => $advData['imei'] ?? '',
                'oaid'         => $advData['oaid'] ?? '',
                'os'           => $advData['os'] ?? '',
                'click_at'     => $item['reg_time'],
                'ad_id'        => $advData['adid'],
                'creative_id'  => $advData['cid'],
                'union_site'   => $advData['union_site'] ?? '',
                'request_id'   => $requestId,
                'type'         => $this->actionType
            ];

            $this->saveAdvClickData($adv,$clickData);
        }

        $this->save([
            'open_id'       => $item['id'],
            'action_time'   => $item['reg_time'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => $requestId,
            'ip'            => $item['device_ip'],
            'action_id'     => $item['id'],
            'matcher'       => $this->product['matcher']
        ],$item);

    }


    public function updateItem($item){
        if(empty($item['channel_id'])) return;

        $info = $this->model->setTableNameWithMonth($item['reg_time'])
            ->where('cp_channel_id','')
            ->where('open_id',$item['openid'])
            ->where('action_time',$item['reg_time'])
            ->first();
        if(empty($info)) return;

        $info->cp_channel_id = $item['channel_id'];
        $data =  $info->data;
        // 补充信息
        $data['replenish'] = ['channel_id',$item['channel_id']];
        $this->data = $data;
        $this->status = ReportStatusEnum::WAITING;
        $info->save();
    }





    public function pushItemPrepare($item){
        $rawData = $item['data'];
        return [
            'product_alias' => $this->product['cp_product_alias'],
            'cp_type'       => $this->product['cp_type'],
            'open_id'       => $item['open_id'],
            'action_time'   => $item['action_time'],
            'cp_channel_id' => $item['cp_channel_id'],
            'ip'            => $item['ip'],
            'ua'            => '',
            'muid'          => $rawData['imei'],
            'device_brand'          => $rawData['device_company'],
            'device_manufacturer'   => '',
            'device_model'          => '',
            'device_product'        => $rawData['device_product'],
            'device_os_version_name'    => '',
            'device_os_version_code'    => $rawData['device_os'],
            'device_platform_version_name'  => '',
            'device_platform_version_code'  => '',
            'android_id'            => $rawData['android_id'],
            'request_id'            => $item['request_id']
        ];
    }





}
