<?php

namespace App\Sdks\Fq\Traits;

use App\Common\Tools\CustomException;

trait Request
{

    /**
     * @param $uri
     * @param array $param
     * @param string $method
     * @param array $header
     * @param array $option
     * @return mixed
     * @throws CustomException
     * 携带认证请求
     */
    public function apiRequest($uri, $param = [], $method = 'GET', $header = [], $option = []){

        $url = $this->getUrl($uri);
        $param['ts'] = time();
        if(empty($param['distributor_id'])){
            $param['distributor_id'] = $this->distributorId;
        }
        $param = $this->sign($param);

        if($method == 'GET'){
            $url .= '?'.http_build_query($param);
            $param = [];
        }

        $result = $this->curlRequest($url, $param, $method, $header, $option);

        $result = json_decode($result,true);
        if(empty($result) || $result['code'] != 200){
            // 错误提示
            $errorMessage = $result['msg'] ?? '番茄请求错误';

            throw new CustomException([
                'code' => 'FQ_REQUEST_ERROR',
                'message' => $errorMessage,
                'log' => true,
                'data' => [
                    'url' => $url,
                    'header' => $header,
                    'param' => $param,
                    'result' => $result,
                ],
            ]);
        }
        return $result;
    }

    /**
     * @param $url
     * @param array $param
     * @param string $method
     * @param array $header
     * @param array $option
     * @return bool|string
     * @throws CustomException
     * CURL请求
     */
    private function curlRequest($url, $param = [], $method = 'GET', $header = [], $option = []){
        $ch = $this->buildCurl($url, $param, $method, $header, $option);

        $result = curl_exec($ch);

        //$info = curl_getinfo($ch);

        $errno = curl_errno($ch);

        if(!!$errno){
            throw new CustomException([
                'code' => 'CURL_REQUEST_ERROR',
                'message' => 'CURL请求错误',
                'log' => true,
                'data' => [
                    'url' => $url,
                    'header' => $header,
                    'param' => $param,
                    'result' => $result,
                    'error' => $errno,
                ],
            ]);
        }

        curl_close($ch);

        return $result;
    }

    /**
     * @param $url
     * @param array $param
     * @param string $method
     * @param array $header
     * @param array $option
     * @return false|resource
     * 构建curl
     */
    private function buildCurl($url, $param = [], $method = 'GET', $header = [], $option = []){
        $method = strtoupper($method);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $header = array_merge($header, ['Connection: close']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        if(stripos($url, 'https://') === 0){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER , false);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if($method == 'POST'){
            curl_setopt($ch, CURLOPT_POST, true);
        }

        $timeout = $option['timeout'] ?? 30;

        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        return $ch;
    }
}
