<?php

namespace App\Sdks\Yw;



use App\Sdks\Yw\Traits\Order;
use App\Sdks\Yw\Traits\Request;

class YwSdk
{

    use Request;
    use Order;

    /**
     * @var
     * 邮箱
     */
    protected $email;

    /**
     * @var
     * 密钥
     */
    protected $secret;

    /**
     * @var
     * 产品标识
     */
    protected $appflags;

    protected $product_type;

    /**
     * @var
     * 版本号
     */
    protected $version = 1;

    /**
     * 公共接口地址
     */
    const BASE_URL = 'https://open.yuewen.com';


    public function __construct($product){
        $this->appflags = $product->cp_product_alias;
        $this->email = $product->account;
        $this->secret = $product->cp_secret;
        $this->product_type = $product->type;
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


    /**
     * 签名参数 sign 算法
     * @param array $params
     * @return array
     */
    public function sign($params = []){
        $appSecret = $this->secret;
        unset($params['sign']);            //去除不参与签名的参数
        ksort($params, SORT_REGULAR);    //根据键值以 ASCII 码升序排序
        $splicedString = '';
        foreach ($params as $paramKey => $paramValue) {
            $splicedString .= $paramKey . $paramValue;
        }
        $params['sign'] = strtoupper(md5($appSecret. $splicedString));
        return  $params;
    }
}
