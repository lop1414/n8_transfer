<?php

namespace App\Services;

use App\Common\Enums\AdvAliasEnum;
use App\Common\Services\BaseService;
use App\Common\Services\SystemApi\CenterApiService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;
use App\Sdks\SecondVersion\SecondVersionSdk;


class ChannelService extends BaseService
{



    protected $advMap =  [
        ''      => '',
        'JRTT'  => AdvAliasEnum::OCEAN,
        'GDT'   => AdvAliasEnum::GDT,
        'BAIDU' => AdvAliasEnum::BAIDU,
        'KUAISHOU' => AdvAliasEnum::KUAISHOU
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
            echo $product['name']. "\n";
            $list = $sdk->getChannel($product['cp_product_alias'],$product['cp_type'],$startTime,$endTime);
            foreach ($list as $item){
                try {

                    $this->createChannelExtend([
                        'product_id' => $product['id'],
                        'cp_channel_id' => $item['custom_alias'],
                        'adv_alias'  => $this->advMap[$item['adv_alias']],
                        'admin_id'  => $adminMap[$item['admin_name']] ?? 0
                    ]);
                }catch(CustomException $e){
                    $errInfo = $e->getErrorInfo(true);
                    echo $errInfo['message']. "\n";
                }catch(\Exception $e){
                    echo $e->getMessage(). "\n";
                }
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
