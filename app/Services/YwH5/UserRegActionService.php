<?php

namespace App\Services\YwH5;


use App\Common\Enums\ReportStatusEnum;
use App\Enums\DataSourceEnums;
use App\Enums\UserActionTypeEnum;
use App\Sdks\Yw\YwSdk;
use App\Services\ProductService;
use App\Services\PullUserActionBaseService;


class UserRegActionService extends PullUserActionBaseService
{

    protected $actionType = UserActionTypeEnum::REG;

    protected $source = DataSourceEnums::CP;

    protected $userFollowActionService;

    protected $ywSdk;

    public function __construct(){
        parent::__construct();
        $this->userFollowActionService = new UserFollowActionService();
    }


    public function setYwSdk(){
        $cpAccount = (new ProductService())->readCpAccount($this->product['cp_account_id']);
        $this->ywSdk = new YwSdk($this->product['cp_product_alias'],$cpAccount['account'],$cpAccount['cp_secret']);
        $this->userFollowActionService->setProduct($this->product);
    }


    public function pullPrepare(){
        $this->setYwSdk();
        $reqPara = [
            'start_time'  => strtotime($this->startTime),
            'end_time'  => strtotime($this->endTime),
            'page'   => 1
        ];

        $data = [];
        do{

            $tmp = $this->ywSdk->getWxUser($reqPara);

            $data = array_merge($data,$tmp['list']);
            $reqPara['page'] += 1;
            $reqPara['next_id'] = $tmp['next_id'];

        }while(count($data) < $tmp['total_count']);

        return $data;
    }


    public function pullItem($item)
    {

        if($item['is_subscribe'] == 1){
            $this->userFollowActionService->pullItem($item);
        }

        $this->save([
            'product_id'    => $this->product['id'],
            'open_id'       => $item['openid'],
            'action_time'   => $item['create_time'],
            'cp_channel_id' => $item['channel_id'],
            'request_id'    => '',
            'ip'            => '',
            'action_id'     => $item['openid'],
            'matcher'       => $this->product['matcher'],
            'extend'        => array_merge([
                'guid'      => $item['guid']
            ],$this->filterExtendInfo($item)),
        ],$item);


    }


    public function updateItem($item){
        if(empty($item['channel_id'])) return;

        $info = $this->model->setTableNameWithMonth($item['create_time'])
            ->where('cp_channel_id','')
            ->where('open_id',$item['openid'])
            ->where('action_time',$item['create_time'])
            ->where('type',$this->actionType)
            ->first();
        if(empty($info)) return;

        $info->cp_channel_id = $item['channel_id'];
        $data =  $info->data;
        // 补充信息
        $data['replenish'] = ['cp_channel_id' => $item['channel_id']];
        $this->data = $data;
        $this->status = ReportStatusEnum::WAITING;
        $info->save();
    }




}
