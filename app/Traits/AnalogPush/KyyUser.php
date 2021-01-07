<?php

namespace App\Traits\AnalogPush;




use App\Sdks\SecondVersion\SecondVersionSdk;

trait KyyUser
{

    public function ywKyyUserAction(){

        $productList = $this->getProductList();

        $sdk = new SecondVersionSdk();

        foreach ($productList as $product){
            if($product['cp_type'] != 'YW' || $product['type'] != 'KYY') continue;

            $this->loopTime(function ($statTime,$endTime) use ($sdk,$product){
               $info = $sdk->getUserAction($product['cp_product_alias'],$product['cp_type'],$statTime,$endTime);

               foreach ($info as $item){
                   switch ($item['type']){
                       case 'ACTIVATION':  // 注册行为
                           $rawData = $item['extend'];
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

                           break;
                   }
               }
            });
        }
    }
}
