<?php

namespace App\Http\Controllers;

use App\Common\Controllers\Front\FrontController;

use App\Common\Enums\StatusEnum;
use App\Common\Models\FailedQueueModel;
use App\Common\Services\DataToQueueService;
use Illuminate\Http\Request;

class TestController extends FrontController
{
    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }



    public function test(Request $request){
        $key = $request->input('key');
        if($key != 'aut'){
            return $this->forbidden();
        }

        $model = new FailedQueueModel();
        do{
            $list = $model
                ->where('queue','queue:USER_ADD_SHORTCUT_ACTION')
                ->where('status',StatusEnum::ENABLE)
                ->skip(0)
                ->take(1000)
                ->get();
            foreach ($list as $item){
                $data = json_decode(json_encode($item['data']),true);
                $queueEnums = str_replace('queue:','', $item['queue']);

                // push to queue
                $service = new DataToQueueService($queueEnums);

                if(isset($data['data']['data']['time']) && strlen($data['data']['data']['time']) == 13){
                    $time = floor($data['data']['data']['time']/1000);
                    $data['data']['action_time'] = date('Y-m-d H:i:s',$time);
                }

                $service->push($data['data']);
                echo $item->id. "|";
                // 删除
                $item->delete();
            }
        }while(!$list->isEmpty());
    }


}
