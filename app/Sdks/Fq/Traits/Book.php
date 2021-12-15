<?php

namespace App\Sdks\Fq\Traits;


trait Book
{

    public function getBookInfo($cpBookId){
        $uri = 'novelsale/openapi/content/book_meta/v1/';
        $param = [
            'book_id'  => $cpBookId
        ];
        return $this->apiRequest($uri,$param);
    }

}
