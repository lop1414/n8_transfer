<?php

namespace App\Traits\AnalogPush;



use App\Sdks\Tw\TwSdk;

trait TwKyy
{

    /**
     * 注册行为
     */
    public function twKyyRegAction(){

        $productList = $this->getProductList();


        foreach ($productList as $product){
            if($product['cp_type'] != 'TW' || $product['type'] != 'KYY') continue;

            $sdk = new TwSdk($product['cp_product_alias'],$product['cp_secret']);


            $this->loopTime(function ($statTime,$endTime) use ($sdk,$product){
               $info = $sdk->getUsers([
                   'reg_time' => date('Y-m-d H:i',strtotime($statTime))
               ]);


               $count = count($info);

               foreach ($info as $i => $item){

                   $this->echoService->progress($count,$i,"{$statTime} ~ {$endTime}");


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
        $productList = $this->getProductList();


        foreach ($productList as $product){
            if($product['cp_type'] != 'YW' || $product['type'] != 'KYY') continue;

            $sdk = new YwSdk($product['cp_product_alias'],$product['account'],$product['cp_secret']);

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
