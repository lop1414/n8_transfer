<?php

namespace App\Services\SecondVersionYwKyy;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\UserActionTypeEnum;
use App\Sdks\SecondVersion\SecondVersionSdk;
use App\Sdks\Yw\YwSdk;
use App\Services\ProductService;
use App\Services\UserActionBaseService;


class UserRegActionService extends UserActionBaseService
{

    protected $actionType = UserActionTypeEnum::REG;

    protected $ywSdk;

    protected $advMap =  [
        ''      => '',
        'JRTT'  => AdvAliasEnum::OCEAN,
        'GDT'   => AdvAliasEnum::GDT,
        'BAIDU' => AdvAliasEnum::BAIDU,
    ];


    public function setYwSdk(){
        $cpAccount = (new ProductService())->readCpAccount($this->product['cp_account_id']);
        $this->ywSdk = new YwSdk($this->product['cp_product_alias'],$cpAccount['account'],$cpAccount['cp_secret']);
    }




    public function pullPrepare(){
        $sdk = new SecondVersionSdk();
        return $sdk->getUserRegAction($this->product['cp_product_alias'],$this->product['cp_type'],$this->startTime,$this->endTime);
    }



    public function pullItem($item){
        $rawData = $item['extend'];

        //渠道的广告商
        $advAlias = $item['channel_info']['adv_alias'] ?? '';

        //点击数据的广告商
        if(empty($advAlias)){
            $advAlias = $item['click']['adv_alias'] ?? '';
        }
        //转发上报数据的广告商
        if(empty($advAlias)){
            $advAlias = $item['forward']['adv_alias'] ?? '';
        }

        $adv = $this->advMap[$advAlias];

        $requestId = $rawData['request_id'] ?? '';
        $clickData = [
            'ip'    => $rawData['ip'] ?? '',
            'ua'    => $rawData['ua'] ? base64_decode($rawData['ua']) : '',
            'muid'         => $rawData['muid'] ?? '',
            'android_id'   => $rawData['android_id'] ?? '',
            'oaid'         => $rawData['oaid'] ?? '',
            'oaid_md5'     => $rawData['oaid_md5'] ?? '',
            'os'           => $rawData['os'] ?? '',
            'click_at'     =>  date('Y-m-d H:i:s',$rawData['time']) ?? '',
        ];


        //转发上报信息
        if(!empty($item['forward'])){
            $clickData['ad_id'] = $item['forward']['extend']['aid'];
            $clickData['creative_id'] = $item['forward']['extend']['cid'];
        }

        //点击信息
        if(!empty($item['click'])){
            $requestId = $item['request_id'];

            $clickData['ad_id'] = $item['click']['ad_id'];
            $clickData['creative_id'] = $item['click']['c_id'];
            $clickData['creative_type'] = $item['click']['c_type'];
            $clickData['link'] = $item['click']['link'];
        }


        if(empty($requestId)){
            $requestId = 'n8_'.md5(uniqid());
        }


        $this->save([
            'open_id'       => $rawData['guid'],
            'action_time'   => $item['valid_info']['act_time'],
            'cp_channel_id' => $item['valid_info']['custom_alias'] ?? '',
            'request_id'    => $requestId,
            'ip'            => $rawData['ip'],
            'action_id'     => $rawData['guid'],
            'matcher'       => $this->product['matcher']
        ],$item);


        // 没有广告商
        if(!empty($adv)){
            $clickData['request_id'] = $requestId;
            $clickData['action_time'] = $item['valid_info']['act_time'];
            $clickData['open_id'] = $rawData['guid'];
            $clickData['action_id'] = $rawData['guid'];
            $clickData['type'] = $this->actionType;
            $this->saveAdvClickData($adv,$clickData);
        }
    }


    public function pullAfter(){

        $this->channelChangeUser();
        $this->replenishCpChannelId();
    }


    /**
     * 渠道变更用户
     */
    public function channelChangeUser(){
        $sdk = new SecondVersionSdk();
        $list = $sdk->getChannelChangeUser($this->product['cp_product_alias'],$this->product['cp_type'],$this->startTime,$this->endTime);

        foreach ($list as $item){
            try{
                $requestId = 'n8_'.md5(uniqid());

                $this->save([
                    'open_id'       => $item['open_id'],
                    'action_time'   => $item['valid_info']['act_time'],
                    'cp_channel_id' => $item['valid_info']['custom_alias'],
                    'action_id'     => $item['open_id'],
                    'ip'            => '',
                    'request_id'    => $requestId,
                    'matcher'       => $this->product['matcher']
                ],$item);

                if(!empty($item['channel_info'])){
                    $advAlias = $item['channel_info']['adv_alias'];
                    $adv = $this->advMap[$advAlias];
                    if(!empty($adv)){
                        $clickData = [
                            'ip'    => $item['user_info']['ip'] ?? '',
                            'ua'    => $item['user_info']['ua'] ?? '',
                            'muid'         => $item['user_info']['muid'] ?? '',
                            'android_id'   => '',
                            'oaid'         => $item['user_info']['oaid'] ?? '',
                            'oaid_md5'     => '',
                            'os'           => '',
                            'click_at'     => $item['change_time'],
                            'request_id'   => $requestId,
                        ];
                        $this->saveAdvClickData($adv,$clickData);
                    }

                }

            }catch (\Exception $e){

                //未命中唯一索引
                if($e->getCode() != 23000){
                    //日志
                    (new ErrorLogService())->catch($e);
                    var_dump($e);
                }else{
                    echo "  命中唯一索引\n";
                }
            }

        }
    }



    /**
     * 补充CP渠道ID
     */
    public function replenishCpChannelId(){
        $this->setYwSdk();
        $list = $this->getReportUserActionList(['cp_channel_id' => 0]);

        foreach ($list as $item){
            try{

                $tmp = $this->ywSdk->getUser([
                    'guid'  => $item['open_id']
                ]);

                if(empty($tmp['list'])){
                    continue;
                }

                $user = $tmp['list'][0];

                // 用户被重新染色 TODO
                if($user['seq_time'] != $item['action_time']){}

                $item->cp_channel_id = $user['channel_id'];
                $item->save();
            }catch(CustomException $e){
                $errorInfo = $e->getErrorInfo(true);

                echo $errorInfo['message']. "\n";

            }catch (\Exception $e){

                echo $e->getMessage(). "\n";

            }
        }

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
            'ua'            => $rawData['user_info']['ua'] ?? '',
            'muid'          => $rawData['user_info']['muid'] ?? '',
            'device_brand'          => '',
            'device_manufacturer'   => '',
            'device_model'          => '',
            'device_product'        => '',
            'device_os_version_name'    => '',
            'device_os_version_code'    => '',
            'device_platform_version_name'  => '',
            'device_platform_version_code'  => '',
            'android_id'            => $rawData['extend']['android_id'] ?? '',
            'request_id'            => $item['request_id']
        ];
    }





}
