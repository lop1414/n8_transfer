<?php

namespace App\Http\Controllers\Open;


use App\Common\Controllers\Open\OpenController;

class BaseController extends OpenController
{

    /**
     * @param $data
     * @return array
     * 过滤设备信息
     */
    public function filterDeviceInfo($data){
        $clickId = '';
        if(isset( $data['adv_click_id'])){
            $clickId = $data['adv_click_id'];
        }

        if(isset( $data['click_id'])){
            $clickId = $data['click_id'];
        }


        return array(
            'ua'                    => $data['ua'] ?? '',
            'muid'                  => $data['muid'] ?? '',
            'oaid'                  => $data['oaid'] ?? '',
            'device_brand'          => $data['device_brand'] ?? '',
            'device_manufacturer'   => $data['device_manufacturer'] ?? '',
            'device_model'          => $data['device_model'] ?? '',
            'device_product'        => $data['device_product'] ?? '',
            'device_os_version_name'=> $data['device_os_version_name'] ?? '',
            'device_os_version_code'=> $data['device_os_version_code'] ?? '',
            'device_platform_version_name' => $data['device_platform_version_name'] ?? '',
            'device_platform_version_code' => $data['device_platform_version_code'] ?? '',
            'android_id'            => $data['android_id'] ?? '',
            'adv_click_id'          => $clickId,
        );
    }
}
