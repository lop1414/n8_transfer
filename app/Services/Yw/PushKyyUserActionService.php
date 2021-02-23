<?php

namespace App\Services\Yw;


use App\Enums\UserActionPushStatusEnum;
use App\Enums\UserActionTypeEnum;
use App\Sdks\N8\N8Sdk;
use App\Services\PullUserActionBaseService;


class PushKyyUserActionService extends PullUserActionBaseService
{

    public $actionType;


    public function commonCode($fn){
        $sdk = new N8Sdk();
        $this->productForeach(function ($product) use ($sdk,$fn){
            $sdk->setSecret($product['secret']);

            $this->loopTime(function ($startTime,$endTime) use ($sdk,$product,$fn){
                $this->echoService->progress(0,0,"{$startTime} ~ {$endTime}");

                $list = $this->getUserActionData($startTime,$endTime,$product['id'],$this->actionType);

                $count = count($list);

                foreach ($list as $i => $item){

                    $this->echoService->progress($count,$i,"{$startTime} ~ {$endTime}");

                    $rawData = $item['data'];

                     // 推送
                     $fn($sdk,$product,$rawData);

                    $item->status = UserActionPushStatusEnum::DONE;
                    $item->save();

                }
                $this->echoService->echo('');
            });
        });

        $this->echoService->echo('');
    }


    public function reg(){

        $this->echoService->echo('阅文快应用：注册');

        $this->actionType = UserActionTypeEnum::REG;
        $this->commonCode(function ($sdk,$product,$rawData){
            $sdk->reportKyyUserReg([
                'open_id'       => $rawData['guid'],
                'product_id'    => $product['id'],
                'reg_time'      => date('Y-m-d H:i:s',$rawData['time']),
                'ip'            => $rawData['ip'],
                'ua'            => base64_decode($rawData['ua']),
                'android_id'    => $rawData['android_id'],
                'request_id'    => $rawData['request_id'],
            ]);
        });

    }


    public function bind_channel(){
        $this->echoService->echo('阅文快应用：绑定渠道');

        $this->actionType = UserActionTypeEnum::BIND_CHANNEL;
        $this->commonCode(function ($sdk,$product,$rawData){
            $sdk->reportKyyUserBindChannel([
                'open_id'       => $rawData['guid'],
                'product_id'    => $product['id'],
                'channel_id'    => $rawData['channel_id'],
                'bind_time'     => $rawData['update_time'],
                'device_manufacturer'    => $rawData['manufacturer']
            ]);
        });
    }




    public function addShortcut(){
        $this->echoService->echo('阅文快应用：加桌');

        $this->actionType = UserActionTypeEnum::ADD_SHORTCUT;
        $this->commonCode(function ($sdk,$product,$rawData){
            $sdk->reportKyyUserShortcut([
                'open_id'       => $rawData['guid'],
                'product_id'    => $product['id'],
                'action_time'   => date('Y-m-d H:i:s',$rawData['time']),
                'ip'            => $rawData['ip'],
                'ua'            => base64_decode($rawData['ua']),
                'android_id'    => $rawData['android_id']
            ]);
        });
    }



    public function order(){

        $this->echoService->echo('阅文快应用：下单');

        $this->actionType = UserActionTypeEnum::ORDER;
        $this->commonCode(function ($sdk,$product,$rawData){

            $sdk->reportKyyUserOrder([
                'open_id'       => $rawData['guid'],
                'product_id'    => $product['id'],
                'order_id'      => $rawData['yworder_id'],
                'order_time'    => $rawData['order_time'],
                'amount'        => $rawData['amount'] * 100,
                'type'          => 'NORMAL'
            ]);
        });
    }



    public function complete_order(){
        $this->echoService->echo('阅文快应用：完成订单');

        $this->actionType = UserActionTypeEnum::COMPLETE_ORDER;

        $this->commonCode(function ($sdk,$product,$rawData){

            $sdk->reportKyyOrderComplete([
                'product_id'    => $product['id'],
                'order_id'      => $rawData['yworder_id'],
                'complete_time'    => $rawData['pay_time']
            ]);
        });
    }

}
