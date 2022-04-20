<?php

namespace App\Services\UserAction;

abstract class UserActionAbstract implements UserActionInterface
{

    public function getTotal(array $product, string $startTime, string $endTime): ?int
    {
        return null;
    }


    /**
     * @param $data
     * @return array
     * 过滤扩展信息
     */
    public function filterExtendInfo($data): array
    {
        return array(
            'click_id'              => $data['click_id'] ?? '',
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
            'android_id'            => $data['android_id'] ?? ''
        );
    }


    /**
     * @param $ip
     * @return bool
     * 是否为ip v6
     */
    public function isIpv6($ip){
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
            return true;
        } else {
            return false;
        }
    }

}
