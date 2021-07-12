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



    public function run(){

        $queue = new CustomQueue($this->queueEnum);

        $productService = new ProductService();

        $productMap = $productService->getProductMap();

        $queue->setConsumeHook(function ($data) use ($productMap,$productService){
            $k = $productService->getMapKey([
                'cp_type'          => $data['cp_type'],
                'cp_product_alias' => $data['cp_product_alias']
            ]);

            $product = $productMap[$k];

            $data['product_id'] = $product['id'];

            (new MatchDataModel())->create($data);
        });

        $queue->consume();

    }







}
