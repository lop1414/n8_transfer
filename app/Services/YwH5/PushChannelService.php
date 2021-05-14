<?php

namespace App\Services\YwH5;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Models\ConfigModel;
use App\Services\PushChannelBaseService;
use App\Spiders\Yw\YwSpider;


class PushChannelService extends PushChannelBaseService
{


    protected $cookie;

    protected $cpType = CpTypeEnums::YW;

    protected $productType =  ProductTypeEnums::H5;



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

        do{
            $channelList = $spider->getSpreadPromotionList($this->startDate,$this->endDate,$page,$recycle);

            foreach ($channelList['list'] as $item){

                $unionForceChapter = $unionChapter = [];

                $book = $spider->getBookInfo($item['cbid']);
                //创建书籍
                $unionBook = $this->unionApiService->apiCreateBook([
                    'cp_type'    => $product['cp_type'],
                    'cp_book_id' => $book['bookInfo']['cbid'],
                    'name'       => $book['bookInfo']['BookName'],
                    'author_name'  => '',
                    'all_words'  => 0,
                    'update_time'  => $book['bookInfo']['NewChapterTime'],
                ]);
                // 创建章节
                foreach ($book['chapterList'] as $chapter){
                    $tmpChapter = $this->unionApiService->apiCreateChapter([
                        'book_id' => $unionBook['id'],
                        'cp_chapter_id' => $chapter['ccid'],
                        'name'  => $chapter['chapterName'],
                        'seq'  =>  $chapter['id']
                    ]);

                    //打开章节
                    if($chapter['ccid'] == $item['ccid']){
                        $unionChapter = $tmpChapter;
                    }

                    //强制章节
                    if($chapter['id'] == $item['force_chapter']){
                        $unionForceChapter = $tmpChapter;
                    }
                }

                // 创建渠道
                $this->unionApiService->apiCreateChannel([
                    'name'       => $item['name'],
                    'product_id' => $product['id'],
                    'cp_channel_id' => $item['id'],
                    'book_id'  => $unionBook['id'],
                    'chapter_id'  => $unionChapter['id'],
                    'force_chapter_id'  => $unionForceChapter['id'],
                    'create_time'  => $item['create_time'],
                    'updated_time'  => $item['create_time'],
                ]);

            }
            $page += 1;
        }while($page <= $channelList['maxPage']);
    }


}
