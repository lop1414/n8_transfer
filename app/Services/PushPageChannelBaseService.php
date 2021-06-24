<?php

namespace App\Services;


use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;



class PushPageChannelBaseService extends BaseService
{


    protected $cpType;

    protected $productType;


    protected $unionApiService;


    public function __construct()
    {
        parent::__construct();
        $this->unionApiService = new UnionApiService();
    }




    public function getProductList(){
        return (new ProductService())->get([
            'cp_type'   => $this->cpType,
            'type'      => $this->productType,
//            'status'    => StatusEnum::ENABLE
        ]);
    }



    public function productItem($product){}


    public function run(){
        $productList = $this->getProductList();

        foreach ($productList as $product){
            echo $product['name']. "\n";

            try{
                $this->productItem($product);
            }catch(CustomException $e){
                $errInfo = $e->getErrorInfo(true);
                echo $errInfo['message']. "\n";
            }catch(\Exception $e){
                echo $e->getMessage(). "\n";
            }
        }
    }




}
