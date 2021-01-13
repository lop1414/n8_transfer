<?php

namespace App\Sdks\N8;


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
     * 设置密钥
     *
     * @param $secret
     */
    public function setSecret($secret){
        $this->secret = $secret;
    }



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
     * @param $param
     * @return mixed
     *
     */
    public function sign($param){
        // sign字段不参与签名
        unset($param['sign']);

        // 按参数名字典排序
        ksort($param);

        // 参数拼接字符串
        $splicedString = '';
        foreach ($param as $paramKey => $paramValue) {
            $splicedString .= $paramKey . $paramValue;
        }

        // 签名
        $param['sign'] = strtoupper(md5($this->secret. $splicedString));

        return $param;
    }
}

