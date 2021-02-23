<?php

namespace App\Services\Yw;

use App\Common\Services\SystemApi\ProductKyyApiService;
use App\Common\Tools\CustomException;
use App\Enums\UserActionTypeEnum;
use App\Sdks\SecondVersion\SecondVersionSdk;
use App\Sdks\Yw\YwSdk;
use App\Services\PullUserActionBaseService;


class PullKyyUserActionService extends PullUserActionBaseService
{

    public $productKyyApiService;

    public function __construct(){
        parent::__construct();
        $this->productKyyApiService = new ProductKyyApiService();
    }



    public function reg(){

        $this->echoService->echo('阅文快应用：注册');

        $sdk = new SecondVersionSdk();

        $this->productForeach(function ($product) use ($sdk){
            $this->loopTime(function ($startTime,$endTime) use ($sdk,$product){
                $this->echoService->progress(0,0,"{$startTime} ~ {$endTime}");

                $info = $sdk->getUserRegAction($product['cp_product_alias'],$product['cp_type'],$startTime,$endTime);
                $count = count($info);

                foreach ($info as $i => $item){
                    $rawData = $item['extend'];

                    $this->echoService->progress($count,$i,"{$startTime} ~ {$endTime}");

                    // 入库
                    $actionTime = date('Y-m-d H:i:s',$rawData['time']);
                    $this->save($product['id'],$rawData['guid'],UserActionTypeEnum::REG,$actionTime,$rawData);
                }
                $this->echoService->echo('');
            });
        });

        $this->echoService->echo('');

    }


    public function bind_channel(){
        $this->echoService->echo('阅文快应用：绑定渠道');
        // 入库
        $this->productForeach(function ($product) {

            $cpAccountInfo = $this->readCpAccount($product['cp_account_id']);
            $sdk = new YwSdk($product['cp_product_alias'],$cpAccountInfo['account'],$cpAccountInfo['cp_secret']);

            $this->loopTime(function ($startTime,$endTime) use ($sdk,$product){

                $param = [
                    'start_time'  => strtotime($startTime),
                    'end_time'    => strtotime($endTime),
                    'page'        => 1
                ];
                $currentTotal = $total = 0;
                do{
                    $this->echoService->progress(0,0,"{$startTime} ~ {$endTime}");
                    $data = $sdk->getUser($param);

                    $count = count($data['list']);

                    foreach ($data['list'] as $i => $item) {
                        $this->echoService->progress($count,$i,"{$startTime} ~ {$endTime}");
                        // 入库
                        $this->saveBindChannelAction($product['id'],$item);
                    }
                    $this->echoService->echo('');

                    $param['last_page'] = $param['page'];
                    $param['page'] += 1;
                    $param['next_id'] = $data['next_id'];
                    $total = $param['total_count'] = $data['total_count'];
                    $currentTotal += $count;
                }while($total > $currentTotal);

            });
        });
    }


    public function addShortcut(){
        $this->echoService->echo('阅文快应用：加桌');


        $sdk = new SecondVersionSdk();

        $this->productForeach(function ($product) use ($sdk){
            $this->loopTime(function ($startTime,$endTime) use ($sdk,$product){
                $this->echoService->progress(0,0,"{$startTime} ~ {$endTime}");

                $info = $sdk->getUserAddShortcutAction($product['cp_product_alias'],$product['cp_type'],$startTime,$endTime);
                $count = count($info);

                foreach ($info as $i => $item){
                    $rawData = $item['extend'];

                    $this->echoService->progress($count,$i,"{$startTime} ~ {$endTime}");

                    // 入库
                    $actionTime = date('Y-m-d H:i:s',$rawData['time']);
                    $this->save($product['id'],$rawData['guid'],UserActionTypeEnum::ADD_SHORTCUT,$actionTime,$rawData);

                }
                $this->echoService->echo('');
            });
        });

        $this->echoService->echo('');
    }



    public function order(){

        $this->echoService->echo('阅文快应用：下单');

        $this->productForeach(function ($product){

            $cpAccountInfo = $this->readCpAccount($product['cp_account_id']);

            $sdk = new YwSdk($product['cp_product_alias'],$cpAccountInfo['account'],$cpAccountInfo['cp_secret']);

            $this->loopTime(function ($startTime,$endTime) use ($sdk,$product){

                $param = [
                    'coop_type'   => 11,
                    'start_time'  => strtotime($startTime),
                    'end_time'    => strtotime($endTime),
                    'page'        => 1
                ];
                $currentTotal = $total = 0;
                do{
                    $this->echoService->progress(0,0,"{$startTime} ~ {$endTime}");
                    $data = $sdk->getOrders($param);
                    $count = count($data['list']);

                    foreach ($data['list'] as $i => $item) {
                        $this->echoService->progress($count,$i,"{$startTime} ~ {$endTime}");
                        // 入库
                        $actionTime = $item['order_time'];
                        $this->save($product['id'],$item['guid'],UserActionTypeEnum::ORDER,$actionTime,$item);
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
        });

        $this->echoService->echo('');
    }



    public function complete_order(){
        $this->echoService->echo('阅文快应用：完成订单');

        $this->productForeach(function ($product) {
            $cpAccountInfo = $this->readCpAccount($product['cp_account_id']);

            $sdk = new YwSdk($product['cp_product_alias'],$cpAccountInfo['account'],$cpAccountInfo['cp_secret']);

            $this->loopTime(function ($startTime,$endTime) use ($sdk,$product){

                $param = [
                    'coop_type'   => 11,
                    'order_status'=> 2,
                    'start_time'  => strtotime($startTime),
                    'end_time'    => strtotime($endTime),
                    'page'        => 1
                ];
                $currentTotal = $total = 0;
                do{
                    $this->echoService->progress(0,0,"{$startTime} ~ {$endTime}");

                    $data = $sdk->getOrders($param);
                    $count = count($data['list']);

                    foreach ($data['list'] as $i => $item) {
                        $this->echoService->progress($count,$i,"{$startTime} ~ {$endTime}");
                        // 入库
                        $actionTime = $item['pay_time'];
                        $this->save($product['id'],$item['guid'],UserActionTypeEnum::COMPLETE_ORDER,$actionTime,$item);
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
        });


        $this->echoService->echo('');
    }



    public function saveBindChannelAction($productId,$data){


        try {
            // 没有绑定渠道
            if(empty($data['channel_id'])){
                return;
            }

            $diff = strtotime($data['update_time']) - strtotime($data['reg_time']);

            // 更新时间跟注册时间相差大于60秒
            if($diff > 60){
                $info = $this->productKyyApiService->apiReadUserBindChannelByOpenId($productId,$data['guid'],$data['channel_id']);

                if(!empty($info)){
                    return;
                }
            }
        }catch (CustomException $e){
            $errInfo = $e->getErrorInfo();


            // 不是找不到用户情况
            if($errInfo->data->result->code != 'NOT_GUID_BY_OPEN_ID'){
                $this->echoService->echo($errInfo->message);
                var_dump($data);
                return;
            }


        }catch (\Exception $e){

            var_dump($e->getMessage());
        }

        // 记录
        $this->save($productId,$data['guid'],UserActionTypeEnum::BIND_CHANNEL,$data['update_time'],$data);

    }

}
