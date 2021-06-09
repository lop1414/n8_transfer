<?php

namespace App\Services;

use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\ConsoleEchoService;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;
use App\Common\Tools\CustomQueue;
use App\Enums\QueueEnums;
use App\Models\MatchDataModel;
use Illuminate\Support\Facades\DB;

class MatchDataToDbService extends BaseService
{

    protected $queueEnum;

    protected $model;


    public function __construct(){
        parent::__construct();
        $this->model = new MatchDataModel();
    }



    public function setQueueEnum($queueEnum){
        Functions::hasEnum(QueueEnums::class,$queueEnum);
        $this->queueEnum = $queueEnum;
        return $this;
    }



    /**
     * @return mixed
     * 获取队列枚举
     */
    public function getQueueEnum(){
        return $this->queueEnum;
    }


    public function getMapKey($product){
        return $product['cp_type'].'_'. $product['cp_product_alias'];
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



    public function run(){

        $queue = new CustomQueue($this->queueEnum);
        $productMap = $this->getProductMap();

        $rePushData = [];
        while ($data = $queue->pull()) {

            try{
                DB::beginTransaction();
                $k = $this->getMapKey([
                    'cp_type'          => $data['cp_type'],
                    'cp_product_alias' => $data['cp_product_alias']
                ]);
                $product = $productMap[$k];

                $data['product_id'] = $product['id'];


                $this->model->create($data);


                DB::commit();

            }catch (CustomException $e){

                DB::rollBack();


                //日志
                (new ErrorLogService())->catch($e);

                $queue->item['exception'] = $e->getErrorInfo();
                $queue->item['code'] = $e->getCode();
                $rePushData[] = $queue->item;

                var_dump($e->getErrorInfo());

                // echo
                (new ConsoleEchoService())->error("自定义异常 {code:{$e->getCode()},msg:{$e->getMessage()}}");
            }catch (\Exception $e){

                DB::rollBack();

                //日志
                (new ErrorLogService())->catch($e);

                $queue->item['exception'] = $e->getMessage();
                $queue->item['code'] = $e->getCode();
                $rePushData[] = $queue->item;

                var_dump($e->getMessage());

                // echo
                (new ConsoleEchoService())->error("异常 {code:{$e->getCode()},msg:{$e->getMessage()}}");
            }
        }

        // 数据重回队列
        foreach ($rePushData as $item){
            $queue->setItem($item);
            $queue->rePush();
        }
    }







}
