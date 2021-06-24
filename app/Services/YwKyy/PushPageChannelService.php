<?php

namespace App\Services\YwKyy;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Models\ConfigModel;
use App\Services\PushPageChannelBaseService;
use App\Spiders\Yw\YwSpider;


class PushPageChannelService extends PushPageChannelBaseService
{


    protected $cookie;

    protected $cpType = CpTypeEnums::YW;

    protected $productType =  ProductTypeEnums::KYY;



    public function __construct(){
        parent::__construct();
        $this->setCookie();
    }


    public function setCookie(){
        $this->cookie = (new ConfigModel())
            ->where('group',CpTypeEnums::YW)
            ->where('k','spider_cookie')
            ->first()
            ->v;
        return $this;
    }




    public function productItem($product){
        $this->item($product);
        $this->item($product,1); //已删除
    }


    /**
     * @param $product
     * @param int $recycle  1 - 获取已删除
     * @throws \App\Common\Tools\CustomException
     */
    public function item($product,$recycle = 0){
        $spider = (new YwSpider($this->cookie))->switchApp($product['name'],$product['type']);


        $page = 1;
        $currentCount = 0;
        do{

            $channelList = $spider->getPageQuickSpreadPromotionList($page,$recycle);
            $count = $channelList['count'];
            $currentCount += count($channelList['list']);
            foreach ($channelList['list'] as $item){

                try{

                    // 创建渠道
                    $this->unionApiService->apiCreateChannel([
                        'name'       => $item['name'],
                        'product_id' => $product['id'],
                        'cp_channel_id' => $item['id'],
                        'book_id'  => 0,
                        'chapter_id'  => 0,
                        'force_chapter_id'  => 0,
                        'create_time'  => $item['create_time'],
                        'updated_time'  => $item['create_time'],
                    ]);


                }catch(CustomException $e){

                    //日志
                    (new ErrorLogService())->catch($e);
                    $errInfo = $e->getErrorInfo(true);
                    echo 'CustomException :'. $errInfo['message']. "  cp_channel_id:{$item['id']}\n";

                }catch(\Exception $e){
                    //日志
                    (new ErrorLogService())->catch($e);

                    echo 'Exception :'. $e->getMessage(). "  cp_channel_id:{$item['id']}\n";
                }
            }
            $page += 1;
        }while($currentCount < $count);
    }


}
