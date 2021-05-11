<?php

namespace App\Spiders\Yw;



use App\Common\Enums\ProductTypeEnums;
use App\Common\Tools\CustomException;
use App\Spiders\Yw\Traits\Book;
use App\Spiders\Yw\Traits\QuickSpread;
use App\Spiders\Yw\Traits\Request;
use App\Spiders\Yw\Traits\Product;
use App\Spiders\Yw\Traits\Spread;

class YwSpider
{

    use Request;
    use Product;
    use QuickSpread;
    use Spread;
    use Book;



    /**
     * @var
     * 密钥
     */
    protected $cookie;

    /**
     * @var
     * 产品标识
     */
    protected $appId = 0;
    protected $coopId = 0;



    /**
     * 公共接口地址
     */
    const BASE_URL = 'https://open.yuewen.com/api';


    public function __construct($cookie){
        $this->cookie = $cookie;
    }



    public function switchApp($appName,$type = ''){

        $this->apiSwitchApp();

        $map = [
            ProductTypeEnums::H5 => 1,
            ProductTypeEnums::KYY => 11,
        ];
        $this->coopId = $map[$type];

        $tmp = $this->getCoopAppList($appName);
        if(empty($tmp['count'])){
            throw new CustomException([
                'code' => 'NOT_APP_ID',
                'message' => "产品不存在{{$appName}}",
            ]);
        }

        $this->appId = $tmp['list'][0]['appid'];

        $this->apiSwitchApp();

        return $this;
    }



    protected function apiSwitchApp(){
        $uri = 'account/switchApp';
        return $this->apiRequest($uri,[
            'appid' => $this->appId,
            'coopid'=> $this->coopId
        ],'POST');

    }


    /**
     * @param $uri
     * @return string
     * 获取请求地址
     */
    public function getUrl($uri){
        return self::BASE_URL .'/'. ltrim($uri, '/');
    }

    /**
     * @param string $path
     * @return string
     * 获取 sdk 路径
     */
    public function getSdkPath($path = ''){
        $path = rtrim($path, '/');
        $sdkPath = rtrim(__DIR__ .'/'. $path, '/');
        return $sdkPath;
    }

}
