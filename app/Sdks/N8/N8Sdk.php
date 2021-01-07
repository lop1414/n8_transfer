<?php

namespace App\Sdks\N8;


use App\Common\Services\OpenApiService;
use App\Sdks\N8\Traits\ReportKyyUserAction;
use App\Sdks\N8\Traits\Request;

class N8Sdk
{
    use Request;
    use ReportKyyUserAction;

    /**
     * @var
     * 密钥
     */
    protected $secret;



    /**
     * @param $uri
     * @return string
     * 获取请求地址
     */
    public function getUrl($uri){
        return env('APP_PRODUCT_API_URL') .'/'. ltrim($uri, '/');
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
        $params['sign'] = (new OpenApiService())->makeSign($params);
        return $params;
    }
}
