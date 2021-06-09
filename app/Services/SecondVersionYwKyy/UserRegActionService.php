<?php

namespace App\Services\SecondVersionYwKyy;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\SecondVersion\SecondVersionSdk;
use App\Sdks\Yw\YwSdk;
use App\Services\ProductService;
use App\Services\PullUserActionBaseService;


class UserRegActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::REG;

    protected $source = DataSourceEnums::SECOND_VERSION;

    protected $ywSdk;

    protected $advMap =  [
        ''      => '',
        'JRTT'  => AdvAliasEnum::OCEAN,
        'GDT'   => AdvAliasEnum::GDT,
        'BAIDU' => AdvAliasEnum::BAI_DU,
        'KUAISHOU' => AdvAliasEnum::KUAI_SHOU,
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

        $cpChannelId = $item['valid_info']['custom_alias'] ?? '';
        $regTime = $item['valid_info']['act_time'];
        //有计划没渠道
        if(!empty($rawData['user_info']['ad_id']) && empty($item['channel_info']) && $item['valid_info']['has_channel'] == 1){

            $cpChannelId = '';

            $tmp = $this->ywSdk->getUser([
                'guid'  => $item['open_id']
            ]);

            if(!empty($tmp['list'])){
                $user = $tmp['list'][0];
                $cpChannelId = $user['channel_id'] ?: '';
            }
        }

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

        //用户信息上的广告商
        if(empty($advAlias)){
            $advAlias = $item['user_info']['adv_alias'] ?? '';
        }

        $adv = $this->advMap[$advAlias];

        $requestId = $rawData['request_id'] ?? '';
        $ua = $rawData['ua'] ?? '';
        if(!empty($ua) && $ua == base64_encode(base64_decode($ua))){
            $ua = base64_decode($ua);
        }

        $clickData = [
            'ip'    => $rawData['ip'] ?? '',
            'ua'    => $ua,
            'muid'         => $rawData['muid'] ?? '',
            'android_id'   => $rawData['android_id'] ?? '',
            'oaid'         => $rawData['oaid'] ?? '',
            'oaid_md5'     => $rawData['oaid_md5'] ?? '',
            'os'           => $rawData['os'] ?? '',
            'click_at'     => $regTime,
        ];


        //转发上报信息
        if(!empty($item['forward'])){
            $clickData['ad_id'] = $item['forward']['extend']['aid'] ?? '';
            $clickData['creative_id'] = $item['forward']['extend']['cid'] ?? '';
        }

        //点击信息
        if(!empty($item['click'])){
            $requestId = $item['request_id'] ?? '';

            $clickData['ad_id'] = $item['click']['extend']['ad_id'] ?? '';
            $clickData['creative_id'] = $item['click']['extend']['c_id'] ?? '';
            $clickData['creative_type'] = $item['click']['extend']['c_type'] ?? '';
            $clickData['link'] = $item['click']['extend']['link'] ?? '';
        }


        if(empty($requestId)){
            $requestId = 'n8_'.md5(uniqid());
        }


        $openId = $rawData['guid'] ?? $rawData['open_id'];
        $cpChannelId = $cpChannelId ?: '';
        $this->save([
            'open_id'       => $openId,
            'action_time'   => $regTime,
            'cp_channel_id' => $cpChannelId,
            'request_id'    => $requestId,
            'ip'            => $rawData['ip'] ?? '',
            'action_id'     => $openId,
            'matcher'       => $this->product['matcher'],
            'extend'        => $this->filterExtendInfo([
                'ua'            => $rawData['user_info']['ua'] ?? '',
                'muid'          => $rawData['user_info']['muid'] ?? '',
                'android_id'    => $rawData['extend']['android_id'] ?? '',
            ])
        ],$item);


        // 没有广告商
        if(!empty($adv)){
            $clickData['request_id'] = $requestId;
            $clickData['open_id'] = $openId;
            $clickData['action_id'] = $openId;
            $clickData['type'] = $this->actionType;
            if($adv != AdvAliasEnum::OCEAN){
                if(!empty($item['click'])){
                    $clickData['rawData'] = $item['click'];
                }

                if(!empty($item['forward'])){
                    $clickData['rawData'] = $item['forward']['extend'];
                }

                $clickData['extends'] = $clickData['rawData'];
            }


            $this->saveAdvClickData($adv,$clickData);
        }
    }


    public function pullAfter(){
        $this->channelChangeUser();
    }


    /**
     * 渠道变更用户
     */
    public function channelChangeUser(){
        echo "渠道变更用户\n";

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
                    'matcher'       => $this->product['matcher'],
                    'extend'        => $this->filterExtendInfo([])
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

            }catch(CustomException $e){
                (new ErrorLogService())->catch($e);

                //日志
                $errorInfo = $e->getErrorInfo(true);
                echo $errorInfo['message']. "\n";

            }catch (\Exception $e){

                //未命中唯一索引
                if($e->getCode() != 23000){
                    //日志
                    (new ErrorLogService())->catch($e);
                    echo $e->getMessage(). "\n";
                }else{
                    echo "  命中唯一索引\n";
                }
            }

        }
    }










}
