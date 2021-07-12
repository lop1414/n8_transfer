<?php

namespace App\Services;

use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;


class ProductService extends BaseService
{


    /**
     * @param array $data
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 获取产品列表
     */
    public function get($data = []){
        return  (new UnionApiService())->apiGetProduct($data);
    }




    /**
     * @param $id
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     */
    public function readCpAccount($id){
        return  (new UnionApiService())->apiReadCpAccount($id);
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
