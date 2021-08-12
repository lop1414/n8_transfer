<?php

namespace App\Sdks\Qy;


use App\Sdks\Qy\Traits\Channel;
use App\Sdks\Qy\Traits\Request;
use App\Sdks\Qy\Traits\User;
use App\Sdks\Qy\Traits\Order;

class QySdk
{
    use Request;
    use Channel;
    use User;
    use Order;

    /**
     * 公共接口地址
     */
    const BASE_URL = 'https://api.zhangwenwenhua.com';
    /**
     * @var
     * token
     */
    protected $token;
    /**
     * @var
     * 版本号
     */
    protected $version = 1;

    public function __construct($token){
        $this->token = $token;
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
