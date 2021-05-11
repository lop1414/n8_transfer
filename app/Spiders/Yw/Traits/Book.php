<?php

namespace App\Spiders\Yw\Traits;


trait Book
{

    public function getBookInfo($bookId){
        $uri = 'wechatspread/chapterSpread';
        $param = [
            'cbid'      => $bookId
        ];
        return $this->apiRequest($uri,$param);
    }

}
