<?php

namespace App\Services;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;
use App\Services\ProductService;
use App\Spiders\Yw\YwSpider;


class PushChannelBaseService extends BaseService
{


    protected $cpType;

    protected $productType;

    protected $startDate,$endDate;

    protected $unionApiService;


    public function __construct()
    {
        parent::__construct();
        $this->unionApiService = new UnionApiService();
    }


    public function setDateRange($start,$end){
        Functions::checkDateRange($start,$end);
        $this->startDate = $start;
        $this->endDate = $end;
        return $this;
    }



    public function getProductList(){
        return (new ProductService())->get([
            'cp_type'   => $this->cpType,
            'type'      => $this->productType
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
