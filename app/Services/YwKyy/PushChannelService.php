<?php

namespace App\Services\YwKyy;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ProductTypeEnums;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Models\ConfigModel;
use App\Services\PushChannelBaseService;
use App\Spiders\Yw\YwSpider;


class PushChannelService extends PushChannelBaseService
{


    protected $cookie;

    protected $cpType = CpTypeEnums::YW;

    protected $productType = ProductTypeEnums::KYY;


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
     * @throws CustomException
     */
    public function item($product,$recycle = 0){
        $spider = (new YwSpider($this->cookie))->switchApp($product['name'],$product['type']);

        $page = 1;
        $currentCount = 0;
        do{

            $channelList = $spider->getQuickSpreadPromotionList($this->startDate,$this->endDate,$page,$recycle);

            $count = $channelList['count'];
            $currentCount += count($channelList['list']);
            foreach ($channelList['list'] as $item){

                try{
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
                        if(!isset($chapter['id'])) continue;

                        $tmpChapter = $this->unionApiService->apiCreateChapter([
                            'book_id' => $unionBook['id'],
                            'cp_chapter_id' => $chapter['ccid'],
                            'name'  => $chapter['chapterName'],
                            'seq'  =>  $chapter['id'] ?? 0
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
                        'chapter_id'  => $unionChapter['id'] ?? 0,
                        'force_chapter_id'  => $unionForceChapter['id'] ?? 0,
                        'create_time'  => $item['create_time'],
                        'updated_time'  => $item['create_time'],
                    ]);


                }catch(CustomException $e){


                    $errInfo = $e->getErrorInfo(true);
                    echo 'CustomException :'. $errInfo['message']. "  cp_channel_id:{$item['id']}\n";

                    // 书籍不存在 拆分推广页面字段
                    if($errInfo['code'] == 'NO_BOOK'){
                        $tmp = explode('》',$item['page_name']);
                        $bookName =  trim($tmp[0],'《');
                        $chapterName = $tmp[1];
                        //创建书籍
                        $unionBook = $this->unionApiService->apiCreateBook([
                            'cp_type'    => $product['cp_type'],
                            'cp_book_id' => $item['cbid'],
                            'name'       => $bookName,
                            'author_name'  => '',
                            'all_words'  => 0,
                            'update_time'  => $item['create_time'],
                        ]);
                        // 章节
                        $chapter = $this->unionApiService->apiCreateChapter([
                            'book_id' => $unionBook['id'],
                            'cp_chapter_id' => $item['ccid'],
                            'name'  => $chapterName,
                            'seq'  => 0
                        ]);
                        // 创建渠道
                        $this->unionApiService->apiCreateChannel([
                            'name'       => $item['name'],
                            'product_id' => $product['id'],
                            'cp_channel_id' => $item['id'],
                            'book_id'  => $unionBook['id'],
                            'chapter_id'  => $chapter['id'] ?? 0,
                            'force_chapter_id'  =>  0,
                            'create_time'  => $item['create_time'],
                            'updated_time'  => $item['create_time'],
                        ]);
                    }else{
                        //日志
                        (new ErrorLogService())->catch($e);
                    }
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
