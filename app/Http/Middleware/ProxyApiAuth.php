<?php

namespace App\Http\Middleware;

use App\Common\Helpers\Functions;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;
use App\Common\Traits\ApiResponse;
use Closure;

class ProxyApiAuth
{
    use ApiResponse;

    /**
     * @param $request
     * @param Closure $next
     * @return bool|mixed
     * @throws CustomException
     */
    public function handle($request, Closure $next)
    {
        $req = $request->all();

        if(empty($req['product_id'])){
            throw new CustomException([
                'code' => 'PRODUCT_ID_IS_EMPTY',
                'message' => '产品id不能为空',
            ]);
        }

        $unionApiService = new UnionApiService();
        $products = $unionApiService->apiGetProduct([
            'id' => $req['product_id'],
        ]);

        $product = current($products);
        if(empty($product)){
            throw new CustomException([
                'code' => 'NOT_FOUND_PRODUCT',
                'message' => '找不到对应产品',
            ]);
        }

        // 验证
        $this->valid($req, $product['secret']);

        // 设置全局数据
        Functions::setGlobalData('product', $product);
        Functions::setGlobalData('proxy_sign', [
            'product_id' => $req['product_id'],
            'timestamp' => $req['timestamp'],
            'sign' => $req['sign'],
        ]);

        // 删除验签字段
        $request->offsetUnset('product_id');
        $request->offsetUnset('timestamp');
        $request->offsetUnset('sign');

        $response = $next($request);

        // 修饰
        $response = $this->format($response);

        return $response;
    }

    /**
     * 构建签名
     *
     * @param $param
     * @param $secret
     * @return string
     */
    public function makeSign($param, $secret){
        // sign 字段不参与签名
        unset($param['sign']);

        // 按参数名字典排序
        ksort($param);

        // 参数拼接字符串
        $splicedString = '';
        foreach ($param as $paramKey => $paramValue) {
            $splicedString .= $paramKey . $paramValue;
        }

        // 签名
        return strtoupper(md5($secret . $splicedString));
    }


    /**
     * 验证
     *
     * @param $param
     * @param $secret
     * @return bool
     * @throws CustomException
     */
    public function valid($param, $secret){
        if(empty($param['timestamp']) || empty($param['sign'])){
            throw new CustomException([
                'code' => 'PARAM_MISSING',
                'message' => '参数缺失',
            ]);
        }

        // 是否调试
        $isDebug = Functions::isDebug();

        if(!$isDebug && TIMESTAMP - $param['timestamp'] > 300){
            throw new CustomException([
                'code' => 'TIMESTAMP_EXPIRED',
                'message' => '请求已失效',
            ]);
        }

        // 签名
        $sign = $this->makeSign($param, $secret);

        if(Functions::isProduction() && $sign != $param['sign']){
            $ret = [
                'code' => 'SIGN_ERROR',
                'message' => '签名错误',
                'log' => true,
            ];

            throw new CustomException($ret);
        }

        return true;
    }

    /**
     * @param $response
     * @return mixed
     * 修饰
     */
    public function format($response){
        $responseData = json_decode(json_encode($response->getData()), true);

        $code = $responseData['code'] ?? '';
        if($code == 'YW_REQUEST_ERROR'){
            if(Functions::isProduction()){
                unset($responseData['data']);
            }

            if(isset($responseData['data']['result']['code'])){
                $responseData['code'] = 'PROXY_'. $responseData['data']['result']['code'];
            }
        }

        $response->setData($responseData);

        return $response;
    }
}
