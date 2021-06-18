<?php

namespace App\Services;

use App\Common\Enums\OperatorEnum;
use App\Common\Services\BaseService;
use App\Common\Services\ConsoleEchoService;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;
use App\Common\Tools\CustomQueue;
use App\Enums\QueueEnums;


class ForwardDataService extends BaseService
{

    protected $queueEnums = QueueEnums::FORWARD_DATA;

    public function isForward($operator){
        return $operator == OperatorEnum::SYS ? false : true;
    }


    public function toQueue($data){
        $queue = new CustomQueue($this->queueEnums);
        $queue->push($data);
    }



    public function forward(){
        $queue = new CustomQueue($this->queueEnums);
        $productMap = $this->getProductMap();
        $rePushData = [];
        while ($data = $queue->pull()) {

            try{
                $k = $this->getMapKey([
                    'cp_type'          => $data['cp_type'],
                    'cp_product_alias' => $data['cp_product_alias']
                ]);
                $product = $productMap[$k];
                if(!$this->isForward($product['operator'])){
                    continue;
                }

                if(empty($product['extends']['operator_url'])){
                    continue;
                }

                $url = $product['extends']['operator_url']. '?'.http_build_query($data);

                $res = json_decode(file_get_contents($url),true);
                if($res['code'] != 0){
                    throw new CustomException([
                        'code' => 'FORWARD_ERROR',
                        'message' => '推送失败:' . $product['operator'],
                        'log' => true,
                        'data' => $data,
                    ]);
                }
            }catch (CustomException $e){

                //日志
                (new ErrorLogService())->catch($e);

                $queue->item['exception'] = $e->getErrorInfo();
                $queue->item['code'] = $e->getCode();
                $rePushData[] = $queue->item;

                (new ConsoleEchoService())->error("自定义异常 {code:{$e->getCode()},msg:{$e->getMessage()}}");
            }catch (\Exception $e){

                //日志
                (new ErrorLogService())->catch($e);

                $queue->item['exception'] = $e->getMessage();
                $queue->item['code'] = $e->getCode();
                $rePushData[] = $queue->item;

                (new ConsoleEchoService())->error("异常 {code:{$e->getCode()},msg:{$e->getMessage()}}");
            }
        }

        // 数据重回队列
        foreach ($rePushData as $item){
            $queue->setItem($item);
            $queue->rePush();
        }
    }


    /**
     * @return array
     * @throws CustomException
     * 获取产品映射
     */
    public function getProductMap(){
        $products = (new UnionApiService())->apiGetProduct();
        $productMap = [];

        foreach ($products as $product){
            $key = $this->getMapKey($product);
            $productMap[$key] = $product;
        }
        return $productMap;
    }


    public function getMapKey($product){
        return $product['cp_type'].'_'. $product['cp_product_alias'];
    }

}
