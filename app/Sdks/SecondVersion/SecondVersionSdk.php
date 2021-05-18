<?php

namespace App\Sdks\SecondVersion;


use App\Sdks\SecondVersion\Traits\Channel;
use App\Sdks\SecondVersion\Traits\Request;
use App\Sdks\SecondVersion\Traits\UserAction;

class SecondVersionSdk
{
    use Request;
    use UserAction;
    use Channel;

    /**
     * @var
     * 密钥
     */
    protected $secret;


    /**
     * 公共接口地址
     */
    const BASE_URL = 'http://ny.7788zongni.com';


    public function __construct(){
        $this->secret = $systemApiConfig = config('common.second_version_api.key');
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
     *
     * @param $params
     * @return mixed
     */
    public function sign($params){
        unset($params['sign']);            //去除不参与签名的参数
        $params['sign'] = md5($this->secret. $params['time']);
        return  $params;
    }
}
