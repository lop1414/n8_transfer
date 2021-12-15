<?php

namespace App\Sdks\Fq;



use App\Sdks\Fq\Traits\AddDesktop;
use App\Sdks\Fq\Traits\Book;
use App\Sdks\Fq\Traits\Channel;
use App\Sdks\Fq\Traits\Order;
use App\Sdks\Fq\Traits\Request;
use App\Sdks\Fq\Traits\User;

class FqSdk
{

    use Request;
    use Channel;
    use User;
    use AddDesktop;
    use Order;
    use Book;

    /**
     * 公共接口地址
     */
    const BASE_URL = 'https://www.changdunovel.com';
    /**
     * @var
     * 密钥
     */
    protected $secret;
    /**
     * @var
     * 分销商标识
     */
    protected $distributorId;

    public function __construct($distributorId,$secret){
        $this->distributorId = $distributorId;
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

        $params['sign'] = strtolower(md5($params['distributor_id'].$this->secret.$params['ts']));

        return  $params;
    }
}
