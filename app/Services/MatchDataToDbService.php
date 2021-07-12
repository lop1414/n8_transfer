<?php

namespace App\Services;

use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;
use App\Common\Tools\CustomQueue;
use App\Enums\QueueEnums;
use App\Models\MatchDataModel;

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
     * @return array
     * @throws CustomException
     * 获取产品映射
     */
    public function getProductMap(){
        $products = (new UnionApiService())->apiGetProduct();
        $productMap = [];

        foreach ($products as $product){
            $key = $product['cp_type'].'_'. $product['cp_product_alias'];
            $productMap[$key] = $product;
        }
        return $productMap;
    }



    public function run(){

        $queue = new CustomQueue($this->queueEnum);
        $productMap = $this->getProductMap();

        $queue->setConsumeHook(function ($data) use ($productMap){
            $k =  $data['cp_type']. '_'.$data['cp_product_alias'];

            $product = $productMap[$k];

            $data['product_id'] = $product['id'];

            (new MatchDataModel())->create($data);
        });

        $queue->consume();

    }







}
