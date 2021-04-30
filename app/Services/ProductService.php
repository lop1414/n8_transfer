<?php

namespace App\Services;

use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\UnionApiService;


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


}
