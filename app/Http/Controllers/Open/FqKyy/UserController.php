<?php


namespace App\Http\Controllers\Open\FqKyy;


use App\Common\Enums\CpTypeEnums;
use App\Common\Services\ErrorLogService;
use App\Enums\DataSourceEnums;
use App\Enums\QueueEnums;
use App\Common\Services\DataToQueueService;
use App\Enums\UserActionTypeEnum;
use App\Http\Controllers\Open\BaseController;
use App\Services\ForwardDataService;
use Illuminate\Http\Request;

class UserController extends BaseController
{

    protected $cpType = CpTypeEnums::FQ;

    /**
     * 平台产品标识
     * @var int
     */
    protected $cpProductAlias = 1715568083108909;

    /**
     * 注册
     * @param Request $request
     * @return mixed
     */
    public function reg(Request $request){

        try {
            $requestData = $request->all();

            $forwardData = array_merge($requestData,['cp_type' => $this->cpType,'appflag' => $this->cpProductAlias]);
            (new ForwardDataService())->toQueue($forwardData);

            $ua = $requestData['user_agent'] ?? '';
            $data = array_merge([
                'cp_type'     => $this->cpType,
                'cp_product_alias' => $this->cpProductAlias,
                'open_id'      => $requestData['device_id'],
                'action_time'  => date('Y-m-d H:i:s',$requestData['buying_timestamp']),
                'type'         => UserActionTypeEnum::REG,
                'cp_channel_id'=> $requestData['promotion_id'] ?? '',
                'request_id'   => $requestData['request_id'] ?? '',
                'ip'           => $requestData['ip'] ?? '',
                'extend'       => array_merge($this->filterDeviceInfo($requestData),['ua' => $ua]),
                'data'         => $requestData,
                'action_id'    => $requestData['device_id'],
                'source'       => DataSourceEnums::CP
            ]);

            $service = new DataToQueueService(QueueEnums::USER_REG_ACTION);
            $service->push($data);

            return $this->_response(0, 'success');
        }catch (\Exception $e){

            //日志
            (new ErrorLogService())->catch($e);
            return $this->_response($e->getCode(), 'fail:'.$e->getMessage());
        }
    }




    /**
     * 加桌行为
     */
    public function addShortcut(Request $request){
        $requestData = $request->all();

        $forwardData = array_merge($requestData,['cp_type' => $this->cpType,'appflag' => $this->cpProductAlias]);
        (new ForwardDataService())->toQueue($forwardData);

        $ua = $requestData['user_agent'] ?? '';

        $data = array_merge([
            'cp_type'      => $this->cpType,
            'cp_product_alias' => $this->cpProductAlias,
            'open_id'      => $requestData['device_id'],
            'action_time'  => date('Y-m-d H:i:s',$requestData['add_desktop_timestamp']),
            'type'         => UserActionTypeEnum::ADD_SHORTCUT,
            'cp_channel_id'=> '',
            'request_id'   => '',
            'ip'           => '',
            'extend'       => array_merge($this->filterDeviceInfo($requestData),['ua' => $ua]),
            'data'         => $requestData,
            'action_id'    => $requestData['device_id'],
            'source'       => DataSourceEnums::CP
        ]);

        $service = new DataToQueueService(QueueEnums::USER_ADD_SHORTCUT_ACTION);
        $service->push($data);
        return $this->success();
    }
}
