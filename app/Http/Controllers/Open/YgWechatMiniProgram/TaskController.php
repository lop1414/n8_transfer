<?php


namespace App\Http\Controllers\Open\YgWechatMiniProgram;


use App\Common\Enums\CpTypeEnums;
use App\Enums\QueueEnums;
use App\Common\Services\DataToQueueService;
use App\Http\Controllers\Open\BaseController;
use Illuminate\Http\Request;

class TaskController extends BaseController
{

    protected $cpType = CpTypeEnums::YG;

    /**
     * 任务回调
     * @param Request $request
     * @return mixed
     */
    public function callback(Request $request){
        $requestData = $request->all();

        $service = new DataToQueueService(QueueEnums::YG_TASK_CALLBACK_DATA);
        $service->push($requestData);

        return $this->_response(0, 'success');

    }

}
