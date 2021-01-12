<?php

namespace App\Traits\AnalogPush;



use App\Sdks\Tw\TwSdk;

trait TwKyy
{

    /**
     * 注册行为
     */
    public function twKyyRegAction(){

        $this->echoService->echo('腾文快应用：注册、加桌行为');

        $productList = $this->getProductList();


        foreach ($productList as $product){
            if($product['cp_type'] != 'TW' || $product['type'] != 'KYY') continue;

            $sdk = new TwSdk($product['cp_product_alias'],$product['cp_secret']);


            $this->loopTime(function ($startTime,$endTime) use ($sdk,$product){
               $info = $sdk->getUsers([
                   'reg_time' => date('Y-m-d H:i',strtotime($startTime))
               ]);


               $count = count($info);

               foreach ($info as $i => $item){

                   $this->echoService->progress($count,$i,"{$startTime} ~ {$endTime}");


                   // 注册行为
                   $this->pushSdk->reportKyyUserReg([
                       'open_id'       => $item['id'],
                       'product_id'    => $product['id'],
                       'reg_time'      => $item['reg_time'],
                       'reg_ip'        => $item['device_ip'],
                       'device_brand'  => $item['device_company'],
                       'device_product'=> $item['device_product'],
                       'device_os_version_code'=> $item['device_os'],
                       'muid'          => $item['imei'],
                       'oaid'          => $item['oaid'],
                       'android_id'    => $item['android_id']
                   ]);

                   //加桌行为
                   if($item['is_save_shortcuts'] == 1){
                       $this->pushSdk->reportKyyUserShortcut([
                           'open_id'       => $item['id'],
                           'product_id'    => $product['id'],
                           'action_time'   => $item['reg_time']
                       ]);
                   }
               }
                $this->echoService->echo('');
            });
        }
    }


    /**
     * 下单行为 , 完成充值行为
     */
    public function twKyyPayAction(){

        $this->echoService->echo('腾文快应用：下单、订单完成');

        $productList = $this->getProductList();

        foreach ($productList as $product){
            if($product['cp_type'] != 'TW' || $product['type'] != 'KYY') continue;

            $sdk = new TwSdk($product['cp_product_alias'],$product['cp_secret']);

            $this->loopTime(function ($startTime,$endTime) use ($sdk,$product){
                $info = $sdk->getOrders([
                    'pay_time' => date('Y-m-d H:i',strtotime($startTime))
                ]);


                $count = count($info);

                $orderTypeMap = [
                    1 => 'NORMAL',
                    2 => 'OTHER',
                    3 => 'ACTIVITY',
                    4 => 'OTHER'
                ];

                foreach ($info as $i => $item){

                    $this->echoService->progress($count,$i,"{$startTime} ~ {$endTime}");


                    // 下单行为
                    $this->pushSdk->reportKyyUserPay([
                        'open_id'       => $item['uid'],
                        'product_id'    => $product['id'],
                        'order_id'      => $item['id'],
                        'order_time'    => $item['created_at'],
                        'amount'        => $item['amount'],
                        'type'          => $orderTypeMap[$item['type']],
                        'ip'            => $item['device_ip'],
                        'imei'          => $item['imei'],
                        'oaid'          => $item['oaid'],
                        'device_product'=> $item['device_product'],
                    ]);

                    //订单完成行为
                    if($item['is_pay'] == 1){
                        $this->pushSdk->reportKyyOrderComplete([
                            'product_id'    => $product['id'],
                            'order_id'      => $item['id'],
                            'complete_time' => $item['finished_at']
                        ]);
                    }
                }
                $this->echoService->echo('');
            });
        }
    }
}
