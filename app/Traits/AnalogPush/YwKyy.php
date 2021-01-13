<?php

namespace App\Traits\AnalogPush;




use App\Sdks\SecondVersion\SecondVersionSdk;
use App\Sdks\Yw\YwSdk;

trait YwKyy
{

    /**
     * 注册 、 加桌行为
     */
    public function ywKyyUserAction(){

        $this->echoService->echo('阅文快应用：注册、加桌行为');

        $productList = $this->getProductList();

        $sdk = new SecondVersionSdk();

        foreach ($productList as $product){
            if($product['cp_type'] != 'YW' || $product['type'] != 'KYY') continue;

            // 设置密钥
            $this->pushSdk->setSecret($product['secret']);

            $this->loopTime(function ($statTime,$endTime) use ($sdk,$product){
               $info = $sdk->getUserAction($product['cp_product_alias'],$product['cp_type'],$statTime,$endTime);
               $count = count($info);

               foreach ($info as $i => $item){
                   $rawData = $item['extend'];

                   $this->echoService->progress($count,$i,"{$statTime} ~ {$endTime}");

                   switch ($item['type']){
                       case 'ACTIVATION':  // 注册行为
                           $this->pushSdk->reportKyyUserReg([
                                'open_id'       => $rawData['guid'],
                                'product_id'    => $product['id'],
                                'reg_time'      => date('Y-m-d H:i:s',$rawData['time']),
                                'reg_ip'        => $rawData['ip'],
                                'reg_ua'        => $rawData['ua'] ? base64_decode($rawData['ua']) : '',
                                'android_id'    => $rawData['android_id'],
                                'request_id'    => $rawData['request_id']
                           ]);

                           break;
                       case 'REGISTER': //加桌行为
                           $this->pushSdk->reportKyyUserShortcut([
                               'open_id'       => $rawData['guid'],
                               'product_id'    => $product['id'],
                               'action_time'   => date('Y-m-d H:i:s',$rawData['time']),
                               'ip'            => $rawData['ip'],
                               'ua'            => $rawData['ua'] ? base64_decode($rawData['ua']) : '',
                               'android_id'    => $rawData['android_id']
                           ]);
                           break;
                   }
               }
                $this->echoService->echo('');
            });
        }
    }


    /**
     * 下单行为 , 完成充值行为
     */
    public function ywKyyUserPay(){

        $this->echoService->echo('阅文快应用：下单、订单完成');

        $productList = $this->getProductList();


        foreach ($productList as $product){
            if($product['cp_type'] != 'YW' || $product['type'] != 'KYY') continue;

            $sdk = new YwSdk($product['cp_product_alias'],$product['account'],$product['cp_secret']);

            // 设置密钥
            $this->pushSdk->setSecret($product['secret']);

            $this->loopTime(function ($startTime,$endTime) use ($sdk,$product){
                $typeMap = [
                    1 => 'NORMAL', // 普通充值
                    2 => 'ANNUAL' // 年费
                ];

                $param = [
                    'coop_type'   => 11,
                    'start_time'  => strtotime($startTime),
                    'end_time'    => strtotime($endTime),
                    'page'        => 1
                ];

                $currentTotal = $total = 0;

                do{
                    $data = $sdk->getOrders($param);
                    $count = count($data['list']);

                    foreach ($data['list'] as $i => $item) {
                        $this->echoService->progress($count,$i,"{$startTime} ~ {$endTime}");

                        if(empty($item['order_id'])){
                            var_dump($item);
                            continue;
                        }

                        $this->pushSdk->reportKyyUserPay([
                            'open_id'       => $item['guid'],
                            'product_id'    => $product['id'],
                            'order_id'      => $item['order_id'],
                            'order_time'    => $item['order_time'],
                            'amount'        => $item['amount'] * 100,
                            'type'          => $typeMap[$item['order_type']],
                        ]);

                        if($item['order_status'] == 2 && !empty($item['pay_time'])){
                            $this->pushSdk->reportKyyOrderComplete([
                                'product_id'    => $product['id'],
                                'order_id'      => $item['order_id'],
                                'complete_time' => $item['pay_time']
                            ]);
                        }
                    }
                    $this->echoService->echo('');

                    $param['last_page'] = $param['page'];
                    $param['page'] += 1;
                    $param['last_min_id'] = $data['min_id'];
                    $param['last_max_id'] = $data['max_id'];
                    $total = $param['total_count'] = $data['total_count'];
                    $currentTotal += $count;
                }while($total > $currentTotal);
            });
        }
    }
}
