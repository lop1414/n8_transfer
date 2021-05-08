<?php

namespace App\Services;

use App\Common\Enums\AdvAliasEnum;
use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\CenterApiService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Sdks\SecondVersion\SecondVersionSdk;


class ChannelService extends BaseService
{



    protected $advMap =  [
        ''      => '',
        'JRTT'  => AdvAliasEnum::OCEAN,
        'GDT'   => AdvAliasEnum::GDT,
        'BAIDU' => AdvAliasEnum::BAIDU,
    ];


    /**
     * @param $startTime
     * @param $endTime
     * @param null $cpType
     * @throws \App\Common\Tools\CustomException
     * 同步二版推广标识信息
     */
    public function pushChannelExtend($startTime,$endTime,$cpType = null){
        $where = [];
        if(!empty($cpType)){
            $where['cp_type'] = $cpType;
        }
        $productList = (new ProductService())->get($where);
        $sdk = new SecondVersionSdk();

        $adminList = (new CenterApiService())->apiGetAdminUsers();
        $adminMap = array_column($adminList,'id','name');
        foreach ($productList as $product){
           $list = $sdk->getChannel($product['cp_product_alias'],$product['cp_type'],$startTime,$endTime);
           foreach ($list as $item){
               $this->createChannelExtend([
                   'product_id' => $product['id'],
                   'cp_channel_id' => $item['custom_alias'],
                   'adv_alias'  => $this->advMap[$item['adv_alias']],
                   'admin_id'  => $adminMap[$item['admin_name']]
               ]);
           }
        }
    }


    /**
     * @param array $data
     * @return mixed
     * @throws \App\Common\Tools\CustomException
     * 创建渠道扩展信息
     */
    public function createChannelExtend($data = []){
        return  (new UnionApiService())->apiCreateChannelExtend($data);
    }





}
