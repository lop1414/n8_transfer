<?php


namespace App\Http\Controllers\Open\YwKyy;


use App\Common\Enums\CpTypeEnums;
use App\Enums\DataSourceEnums;
use App\Enums\QueueEnums;
use App\Common\Services\DataToQueueService;
use App\Http\Controllers\Open\BaseController;
use Illuminate\Http\Request;

class UserController extends BaseController
{


    /**
     * @param Request $request
     * 分配行为
     */
    public function distribute(Request $request){
        $requestData = $request->all();
        $requestData['source'] = DataSourceEnums::CP;
        $type = $requestData['type'];

        if($type == 'REGISTER'){

            $this->reg($requestData);
        }elseif ($type == 'ADD_DESKTOP'){

            $this->addShortcut($requestData);
        }
    }


    /**
     * @param $requestData
     * @return mixed
     * 注册
     */
    public function reg($requestData){
        $data = array_merge([
            'cp_type'     => CpTypeEnums::YW,
            'cp_product_alias' => $requestData['appflag'],
            'open_id'      => $requestData['guid'],
            'ip'           => $requestData['ip'] ?? '',
            'ua'           => base64_decode($requestData['ua'] ?? ''),
            'action_time'  => date('Y-m-d H:i:s',$requestData['time']),
            'cp_channel_id'=> $requestData['channel_id'] ?? '',
            'request_id'   => $requestData['request_id'] ?? '',
            'rawData'      => $requestData
        ],$this->filterDeviceInfo($requestData));

        $service = new DataToQueueService(QueueEnums::USER_REG_ACTION);
        $service->push($data);

        return $this->success();
    }




    /**
     * @param $requestData
     * @return mixed
     * 加桌行为
     */
    public function addShortcut($requestData){

        $data = array_merge([
            'cp_type'     => CpTypeEnums::YW,
            'cp_product_alias' => $requestData['appflag'],
            'open_id'      => $requestData['guid'],
            'ip'           => $requestData['ip'] ?? '',
            'ua'           => base64_decode($requestData['ua'] ?? ''),
            'action_time'  => date('Y-m-d H:i:s',$requestData['time']),
            'cp_channel_id'=> $requestData['channel_id'] ?? '',
            'request_id'   => $requestData['request_id'] ?? '',
            'rawData'      => $requestData
        ],$this->filterDeviceInfo($requestData));

        $service = new DataToQueueService(QueueEnums::USER_ADD_SHORTCUT_ACTION);
        $service->push($data);
        return $this->success();
    }




    /**
     * @param $data
     * @return array
     * 过滤设备信息
     */
    public function filterDeviceInfo($data){
        return array(
            'ip'                    => $data['ip'] ?? '',
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
            'request_id'            => $data['request_id'] ?? ''
        );
    }


}
