<?php

namespace App\Services;


use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\DataSourceEnums;
use App\Models\UserActionLogModel;
use App\Services\UserAction\AddShortcut\BmKyyAddShortcutService;
use App\Services\UserAction\Order\BmdjWeChatMiniProgramOrderService;
use App\Services\UserAction\Order\BmKyyOrderService;
use App\Services\UserAction\Order\FqKyyOrderService;
use App\Services\UserAction\Order\HsDjGzhOrderService;
use App\Services\UserAction\Order\MbDyMiniProgramOrderService;
use App\Services\UserAction\Order\MbWeChatMiniProgramOrderService;
use App\Services\UserAction\Order\QrWeChatMiniProgramOrderService;
use App\Services\UserAction\Order\QyH5OrderService;
use App\Services\UserAction\Order\TwAppOrderService;
use App\Services\UserAction\Order\TwH5OrderService;
use App\Services\UserAction\Order\TwKyyOrderService;
use App\Services\UserAction\Order\YgWeChatMiniProgramOrderService;
use App\Services\UserAction\Order\YwH5OrderService;
use App\Services\UserAction\Order\YwKyyOrderService;
use App\Services\UserAction\Order\ZyH5OrderService;
use App\Services\UserAction\Order\ZyKyyOrderService;
use App\Services\UserAction\Reg\BmdjWeChatMIniProgramRegService;
use App\Services\UserAction\Reg\BmKyyRegService;
use App\Services\UserAction\Reg\FqKyyRegService;
use App\Services\UserAction\Reg\HsDjGzhRegService;
use App\Services\UserAction\Reg\MbDyMiniProgramRegService;
use App\Services\UserAction\Reg\MbWeChatMiniProgramRegService;
use App\Services\UserAction\Reg\QrWeChatMIniProgramRegService;
use App\Services\UserAction\Reg\QyH5RegService;
use App\Services\UserAction\Reg\TwAppRegService;
use App\Services\UserAction\Reg\TwH5RegService;
use App\Services\UserAction\Reg\TwKyyRegService;
use App\Services\UserAction\Reg\YwH5RegService;
use App\Services\UserAction\Reg\YwKyyRegService;
use App\Services\UserAction\Reg\ZyH5RegService;
use App\Services\UserAction\Reg\ZyKyyRegService;
use App\Services\UserAction\UserActionInterface;

class UserActionService
{
    /**
     * @var
     * 参数
     */
    private $param;

    private $service;

    private $model;


    public function __construct(UserActionInterface $service)
    {
        $this->service = $service;
        $this->model = new UserActionLogModel();
    }


    /**
     * @return string[]
     * 获取需要拉取数据的 服务列表
     */
    static public function getServices(): array
    {
        return [
            YwH5RegService::class,
            TwAppRegService::class,
            BmKyyRegService::class,
            TwKyyRegService::class,
            TwH5RegService::class,
            ZyH5RegService::class,
//            QyH5RegService::class,
            FqKyyRegService::class,
            ZyKyyRegService::class,
            MbDyMiniProgramRegService::class,
            MbWeChatMiniProgramRegService::class,
            HsDjGzhRegService::class,
            QrWeChatMIniProgramRegService::class,
            BmdjWeChatMIniProgramRegService::class,

            BmKyyAddShortcutService::class,

            YwKyyOrderService::class,
            YwH5OrderService::class,
            TwAppOrderService::class,
            TwKyyOrderService::class,
            TwH5OrderService::class,
            ZyH5OrderService::class,
            FqKyyOrderService::class,
            ZyKyyOrderService::class,
            MbDyMiniProgramOrderService::class,
            MbWeChatMiniProgramOrderService::class,
//            QyH5OrderService::class,
            BmKyyOrderService::class,
            HsDjGzhOrderService::class,
            QrWeChatMiniProgramOrderService::class,
            BmdjWeChatMiniProgramOrderService::class,
            YgWeChatMiniProgramOrderService::class,
        ];
    }

    /**
     * @return string[]
     * 获取需要监测差异的服务列表
     */
    static public function getNeedCheckDiffService(): array
    {
        return [
            YwKyyOrderService::class,
            YwH5OrderService::class,
            FqKyyRegService::class,
//            HsDjGzhOrderService::class,
//            HsDjGzhRegService::class,
        ];
    }

    /**
     * @return string[]
     * 获取需要补充渠道的服务列表
     */
    static public function getNeedFillChannelService():array
    {
        return [
            YwKyyRegService::class,
            YwH5RegService::class,
        ];
    }




    /**
     * @param string $key
     * @return mixed|null
     * 获取参数
     */
    public function getParam(string $key)
    {
        if(empty($this->param[$key])){
            return null;
        }
        return $this->param[$key];
    }

    /**
     * @param string $key
     * @param $data
     * 设置参数
     */
    public function setParam(string $key,$data)
    {
        $this->param[$key] = $data;
    }


    public function __call($name, $arguments)
    {
        return $this->service->$name(...$arguments);
    }


    /**
     * @return array
     * @throws CustomException
     * 根据参数获取产品列表
     */
    private function getProducts(): array
    {
        $where = [
            'cp_type'   => $this->service->getCpType(),
            'type'      => $this->service->getProductType(),
            'status'    => StatusEnum::ENABLE
        ];
        if(!empty($this->getParam('product_id'))){
            $where['id'] = $this->getParam('product_id');
        }

        $productService = new ProductService();
        $productList = $productService->get($where);
        foreach ($productList as &$item){
            $item['cp_account'] = $productService->readCpAccount($item['cp_account_id']);
        }

        return $productList;
    }


    public function save($item){
        try{
            $item['source'] = DataSourceEnums::CP;
            $item['status'] = ReportStatusEnum::WAITING;
            $this->model->setTableNameWithMonth($item['action_time'])->create($item);

        }catch (\Exception $e){
            if($e->getCode() == 23000){
                echo "        命中唯一索引 \n";
                return ;
            }

            // 日志
            (new ErrorLogService())->catch($e);
            echo $e->getMessage()."\n";
        }
    }


    /**
     * @param array $product
     * @param string $startTime
     * @param string $endTime
     * 按产品同步
     */
    public function syncByProduct(array $product,string $startTime,string $endTime){

        $data = $this->service->get($product,$startTime,$endTime);

        foreach ($data as $item){
            $item['product_id'] = $product['id'];
            $item['matcher'] = $product['matcher'];
            $this->save($item);
        }
    }

    /**
     * @throws CustomException
     * 同步
     */
    public function sync()
    {
        $productList = $this->getProducts();

        $startTime = $this->getParam('start_time');
        $endTime = $this->getParam('end_time');

        foreach ($productList as $product){
            try{
                echo "    {$product['name']}\n";

                $this->syncByProduct($product,$startTime,$endTime);

            }catch (CustomException $e){

                //日志
                (new ErrorLogService())->catch($e);

            }catch (\Exception $e){

                (new ErrorLogService())->catch($e);
            }

        }
    }



    /**
     * @param array $product
     * @param string $startTime
     * @param string $endTime
     * @return int
     * 按产品获取差异 接口获取的总数跟入库的总数对比
     */
    public function getDiffByProduct(array $product,string $startTime,string $endTime): int
    {
        $actionType = $this->service->getType();
        $dbCount = (new UserActionLogModel())
            ->setTableNameWithMonth($startTime)
            ->whereBetween('action_time',[$startTime,$endTime])
            ->where('product_id',$product['id'])
            ->where('type',$actionType)
            ->count();

        $total = $this->service->getTotal($product,$startTime,$endTime);

        $diff = 0;
        if($total > $dbCount){
            $diff = $total - $dbCount;
        }
        return $diff;
    }


    /**
     * @throws CustomException
     * 检测差异并同步
     */
    public function checkDiffWithSync(){
        $productList = $this->getProducts();

        $startTime = $this->getParam('start_time');
        $endTime = $this->getParam('end_time');

        foreach ($productList as $product){
            echo "    {$product['name']}\n";

            $diff = $this->getDiffByProduct($product,$startTime,$endTime);
            if($diff > 0){
                echo "        相差{$diff} \n";

                if($this->service->getCpType() == CpTypeEnums::YW){
                    //阅文接口频率限制
                    sleep(60);
                }
                $this->syncByProduct($product,$startTime,$endTime);
            }
        }
    }



    // 补充用户渠道
    public function fillUserChannel(){
        $productList = $this->getProducts();
        $startTime = $this->getParam('start_time');
        $endTime = $this->getParam('end_time');

        $userActionLogModel = new UserActionLogModel();

        foreach ($productList as $product){
            echo "    {$product['name']}\n";

            $data = $this->service->get($product,$startTime,$endTime);

            foreach($data as $item){
                try{
                    if(empty($item['cp_channel_id'])){
                        echo "        {$item['open_id']} 没有渠道\n";
                        continue;
                    }

                    $actions = $userActionLogModel
                        ->setTableNameWithMonth($item['action_time'])
                        ->where('open_id',strval($item['open_id']))
                        ->where('product_id',$product['id'])
                        ->where('type',$this->service->getType())
                        ->where('cp_channel_id','')
                        ->get();

                    foreach ($actions as $action){
                        $action->cp_channel_id = $item['cp_channel_id'];

                        // 未上报
                        if($action->status == ReportStatusEnum::WAITING){
                            $action->save();
                            echo "        渠道更新:".$action->open_id. "\n";
                        }else{
                            $rawData = $item['data'];
                            $rawData['action_log_id'] = $action->id;

                            $info = $action->toArray();
                            $info['source'] = DataSourceEnums::CP_PULL;
                            $info['status'] = ReportStatusEnum::WAITING;
                            $info['data'] = $rawData;

                            $this->model->setTableNameWithMonth($info['action_time'])->create($info);
                            echo "        创建新的行为:".$action->open_id. "\n";
                        }
                    }

                }catch(CustomException $e){
                    (new ErrorLogService())->catch($e);

                    //日志
                    $errorInfo = $e->getErrorInfo(true);
                    echo $errorInfo['message']. "\n";

                }catch (\Exception $e){

                    //未命中唯一索引
                    if($e->getCode() != 23000){
                        //日志
                        (new ErrorLogService())->catch($e);
                        echo $e->getMessage()."\n";
                    }else{
                        echo "        命中唯一索引 \n";
                    }
                }
            }
        }
    }


}
