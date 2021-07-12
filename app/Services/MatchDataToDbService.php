<?php

namespace App\Services;

use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
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

        $productService = (new ProductService())->setMap();


        $queue->setConsumeHook(function ($data) use ($productService){
            $product = $productService->readByMap($data['cp_type'], $data['cp_product_alias']);

            $data['product_id'] = $product['id'];

            (new MatchDataModel())->create($data);
        });

        $queue->consume();

    }







}
