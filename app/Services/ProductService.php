<?php

namespace App\Services;

use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;


class ProductService extends BaseService
{

    public $map = [];


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
     * @return $this
     * @throws CustomException
     * 设置产品映射
     */
    public function setMap(){
        if(empty($this->productMap)){
            $products = (new UnionApiService())->apiGetProduct();
            $productMap = [];

            foreach ($products as $product){
                $key = $this->getMapKey($product['cp_type'],$product['cp_product_alias']);
                $productMap[$key] = $product;
            }
            $this->map = $productMap;
        }
        return $this;

    }

    /**
     * @param $cpType
     * @param $cpProductAlias
     * @return mixed
     * 通过缓存的映射数据获取产品信息
     */
    public function readByMap($cpType,$cpProductAlias){
        $k = $this->getMapKey($cpType,$cpProductAlias);
        return $this->map[$k];
    }




    public function getMapKey($cpType,$cpProductAlias){
        return $cpType.'_'. $cpProductAlias;
    }


}
