<?php

namespace App\Sdks\Tw;




use App\Sdks\Tw\Traits\Request;
use App\Sdks\Tw\Traits\User;

class TwSdk
{

    use Request;
    use User;


    /**
     * @var
     * 密钥
     */
    protected $secret;

    /**
     * @var
     * 应用包ID
     */
    protected $packageId;




    /**
     * 公共接口地址
     */
    const BASE_URL = 'https://api.tengwen018.com/package';


    public function __construct($packageId,$secret){
        $this->packageId = $packageId;
        $this->secret = $secret;
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
        $tmp = $this->packageId. $appSecret. $params['time'];
        $params['sign'] = md5($tmp);
        return $params;
    }
}
