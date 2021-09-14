<?php

namespace App\Http\Controllers;

use App\Common\Controllers\Front\FrontController;


use App\Common\Enums\AdvAliasEnum;
use App\Common\Services\SystemApi\UnionApiService;
use App\Enums\UserActionTypeEnum;
use App\Models\KuaiShouClickModel;
use App\Models\MatchDataModel;
use App\Services\AdvClick\SaveClickDataService;
use App\Services\MatchDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $matchDataModel = new MatchDataModel();

        $lastId = 0;
        $channels= [];
        $matchDataService = new MatchDataService();
        do{
            $list = $matchDataModel
                ->where('adv_alias','KS')
                ->where('type','ADD_SHORTCUT')
                ->where('id','>',$lastId)
                ->take(1000)
                ->get();

            foreach ($list as $data){
                $lastId = $data['id'];

                $rawData = $data['data'];
                $openId = $rawData['raw_data']['guid'];
                $cpChannelId = $data['cp_channel_id'];

                $matchDataService->setDataRange('2021-08-09','2021-09-14');
                $info = $matchDataService->getRegLogInfo($data['product_id'],$data['open_id']);

                if(empty($info)){
                    echo "kuai_shou:没有log: {$data['open_id']} \n";
                    continue;
                }


                $requestId = 'n8_'.md5(uniqid());

                $clickAt =  isset($data['data']['ts'])
                    ? date('Y-m-d H:i:s',intval($data['data']['ts']/1000))
                    : $info['action_time'];

                $rawData['raw_data'] = $rawData['extends'];
                unset($rawData['extends']);
                $rawData['match_data_id'] = $data['id'];
                (new SaveClickDataService())
                    ->saveAdvClickData(AdvAliasEnum::KS,[
                        'ip'           => $info['ip'],
                        'ua'           => $info['extend']['ua'],
                        'click_at'     => $clickAt,
                        'type'         => UserActionTypeEnum::REG,
                        'product_id'   => $data['product_id'],
                        'request_id'   => $requestId,
                        'extends'      => $rawData,
                    ]);


                // 获取渠道id
                if(empty($channels[$cpChannelId])){
                    $channels[$cpChannelId] = (new UnionApiService())->apiReadChannel([
                        'product_id' => $data['product_id'],
                        'cp_channel_id' => $cpChannelId,
                    ]);
                }

                $channelId = $channels[$cpChannelId]['id'];

                // 更改联运用户 request_id
                $globalUser = DB::select("SELECT * FROM n8_union.n8_global_users WHERE product_id = {$data['product_id']} AND open_id= {$openId}");
                if(empty($globalUser)) continue;
                $globalUser = $globalUser[0];

                $unionUser = DB::select("SELECT * FROM n8_union.n8_union_users WHERE n8_guid = {$globalUser->n8_guid} AND channel_id= {$channelId} AND click_id = 0");
                if(empty($unionUser)) continue;
                $unionUser = $unionUser[0];

                DB::update("UPDATE n8_union.n8_union_user_extends SET request_id = '{$requestId}' WHERE uuid = {$unionUser->id}");
                echo $unionUser->id."\n";
                die;
            }

        }while(!$list->isEmpty());
    }


}
