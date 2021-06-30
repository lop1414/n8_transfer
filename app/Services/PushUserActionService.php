<?php

namespace App\Services;


use App\Common\Enums\MatcherEnum;
use App\Common\Enums\OperatorEnum;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Helpers\Functions;
use App\Common\Services\BaseService;
use App\Common\Tools\CustomException;
use App\Enums\UserActionTypeEnum;
use App\Models\UserActionLogModel;
use App\Sdks\N8\N8Sdk;
use Illuminate\Support\Facades\DB;


class PushUserActionService extends BaseService
{


    /**
     * @var
     * 行为类型
     */
    protected $actionType;



    /**
     * @var
     * 产品信息
     */
    protected $product;




    /**
     * @var N8Sdk
     */
    protected $n8Sdk;


    /**
     * @var float|int
     * 上报没有渠道的注册行为 时间差 （没有渠道等1小时后再报）
     */
    protected $reportNoChannelDiffTime = 60 * 60;


    /**
     * @var
     * 产品映射
     */
    protected $productMap;


    public function __construct(){
        parent::__construct();
        $this->model = new UserActionLogModel();
        $this->n8Sdk = new N8Sdk();
        $this->setProductMap();
    }


    public function getReportNoChannelDiffTime(){
        return $this->reportNoChannelDiffTime;
    }


    public function setActionType($type){
        $this->actionType = $type;
        return $this;
    }



    public function setProductMap(){

        $product = (new ProductService())->get();
        $this->productMap = array_column($product,null,'id');

    }




    /**
     * @param $productId
     * @return $this
     * 设置产品
     */
    public function setProduct($productId){
        $this->product = $this->productMap[$productId];
        return $this;
    }




    /**
     * @param $startTime
     * @param $endTime
     * @throws CustomException
     * 上报
     */
    public function push($startTime,$endTime){

        if(date('m',strtotime($startTime)) != date('m',strtotime($endTime))){
            throw new CustomException([
                'code' => 'DATE_TIME_ERROR',
                'message' => '月份不一致',
            ]);
        }

        $list = $this->model
            ->setTableNameWithMonth($startTime)
            ->when($this->actionType,function ($query,$actionType){
                return $query->where('type',$actionType);
            })
            ->when($this->product,function ($query,$product){
                return $query->where('product_id',$product['id']);
            })
            ->where('status',ReportStatusEnum::WAITING)
            ->whereBetween('action_time',[$startTime,$endTime])
            ->orderBy('action_time')
            ->get();

        foreach ($list as $item){

          $this->pushItem($item);
        }
    }



    /**
     * 上报所有
     */
    public function pushAll(){
        $sql = <<<STR
SELECT
	`table_name`
FROM
	information_schema.TABLES
WHERE
	TABLE_SCHEMA = 'n8_transfer'
	AND `table_name` LIKE 'user_action_logs_20%'
ORDER BY
	`table_name`
	DESC
STR;
        $tableList = DB::select($sql);
        $tableList = array_column(json_decode(json_encode($tableList),true),'table_name');

        $pushUserActionService = new PushUserActionService();

        foreach ($tableList as $tableName){
            $query = $this->model
                ->setTable($tableName)
                ->when($this->actionType,function ($query,$actionType){
                    return $query->where('type',$actionType);
                })
                ->when($this->product,function ($query,$product){
                    return $query->where('product_id',$product['id']);
                })
                ->where('status',ReportStatusEnum::WAITING);

            $total = $query->count();
            echo $tableName. " 总数：{$total}\n";

            $lastMaxId = 0;
            do{
                $list =  $query
                    ->where('id','>',$lastMaxId)
                    ->skip(0)
                    ->take(1000)
                    ->orderBy('id')
                    ->get();

                foreach ($list as $item){
                    $pushUserActionService
                        ->setProduct($item['product_id'])
                        ->setActionType($item['type'])
                        ->pushItem($item);

                    $lastMaxId = $item['id'];
                }

            }while(!$list->isEmpty());
        }
    }





    public function pushItem($item){
        try{
            // 运营方不是系统 无需上报
            if($this->product['operator'] != OperatorEnum::SYS){
                $item->status = ReportStatusEnum::NOT_REPORT;
                return;
            }else{
                // 注册行为
                if($item['type'] == UserActionTypeEnum::REG && !$this->reportValid($item)){
                    return;
                }
                $action = 'report';
                $action .= ucfirst(Functions::camelize($item['type']));
                $pushData = array_merge($item['extend'],[
                    'product_alias' => $this->product['cp_product_alias'],
                    'cp_type'       => $this->product['cp_type'],
                    'open_id'       => $item['open_id'],
                    'action_time'   => $item['action_time'],
                    'cp_channel_id' => $item['cp_channel_id'],
                    'ip'            => $item['ip'],
                    'request_id'    => $item['request_id']
                ]);

                $this->n8Sdk->setSecret($this->productMap[$item['product_id']]['secret']);
                $this->n8Sdk->$action($pushData);
                $item->status = ReportStatusEnum::DONE;
            }



        }catch(CustomException $e){
            $errorInfo = $e->getErrorInfo(true);

            $item->fail_data = $errorInfo;
            $item->status = ReportStatusEnum::FAIL;
            echo $errorInfo['message']. "\n";

        }catch(\Exception $e){

            $errorInfo = [
                'code'      => $e->getCode(),
                'message'   => $e->getMessage()
            ];

            $item->fail_data = $errorInfo;
            $item->status = ReportStatusEnum::FAIL;
            echo $e->getMessage(). "\n";
        }

        $item->save();
    }



    /**
     * @param $item
     * @return bool
     * 上报验证
     */
    public function reportValid($item){
        //没有渠道
        if(empty($item['cp_channel_id'])){
            $diff = time() - strtotime($item['action_time']);
            if($diff <= $this->reportNoChannelDiffTime){
                return false;
            }
        }

        //不是系统匹配且没有request_id
        if($item['matcher'] != MatcherEnum::SYS && empty($item['request_id'])){
            $diff = time() - strtotime($item['created_at']);
            if($diff < 60*60*2){
                return false;
            }
        }

        return true;
    }



}
