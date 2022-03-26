<?php

namespace App\Sdks\TwApp;


use App\Sdks\TwApp\Traits\CpChannel;
use App\Sdks\TwApp\Traits\Order;
use App\Sdks\TwApp\Traits\Package;
use App\Sdks\TwApp\Traits\Request;
use App\Sdks\TwApp\Traits\User;

class TwAppSdk
{

    use Request;
    use Package;
    use CpChannel;
    use User;
    use Order;


    /**
     * @var
     * 密钥
     */
    protected $secret;

    /**
     * @var
     * CP产品ID
     */
    protected $appId;



    /**
     * 公共接口地址
     */
    const BASE_URL = 'https://api.zhongyue001.com';


    public function __construct($appId,$secret){
        $this->appId = $appId;
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


    public function getSign($appInfo){
        $str = json_encode($appInfo);
        $encrypt = @openssl_encrypt($str, 'aes-256-cbc', $this->secret);
        $token = $this->appId.'-'.$encrypt;
        return $token;
    }

}
