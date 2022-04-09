<?php

namespace App\Services;


use App\Common\Enums\ReportStatusEnum;
use App\Common\Services\ErrorLogService;
use App\Common\Tools\CustomException;
use App\Enums\DataSourceEnums;
use App\Models\UserActionLogModel;
use App\Services\UserAction\Order\YwH5OrderService;
use App\Services\UserAction\Order\YwKyyOrderService;
use App\Services\UserAction\Reg\YwH5RegService;
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
     * 获取服务列表
     */
    static public function getServices(): array
    {
        return [
            YwH5RegService::class,
            YwKyyOrderService::class,
            YwH5OrderService::class,
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
     * @param array $product
     * @param string $startTime
     * @param string $endTime
     * 按产品同步
     */
    public function syncByProduct(array $product,string $startTime,string $endTime){
        $data = $this->service->get($product,$startTime,$endTime);

        foreach ($data as $item){

            try{
                $item['product_id'] = $product['id'];
                $item['matcher'] = $product['matcher'];
                $item['source'] = DataSourceEnums::CP;
                $item['status'] = ReportStatusEnum::WAITING;
                $this->model->setTableNameWithMonth($item['action_time'])->create($item);

            }catch (\Exception $e){
                if($e->getCode() == 23000){
                    echo "  命中唯一索引 \n";
                    continue;
                }

                // 日志
                (new ErrorLogService())->catch($e);
                echo $e->getMessage()."\n";
            }
        }
    }

    /**
     * @throws CustomException
     * 同步
     */
    public function sync()
    {
        $where = [
            'product_ids' => $this->getParam('product_ids'),
            'cp_type'   => $this->service->getCpType(),
            'type'      => $this->service->getType(),
        ];

        $productService = new ProductService();
        $productList = $productService->get($where);

        $startTime = $this->getParam('start_time');
        $endTime = $this->getParam('end_time');

        foreach ($productList as $product){
            try{
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
     * 按产品获取差异
     */
    public function getDiffByProduct(array $product,string $startTime,string $endTime): int
    {
        $total = $this->service->getTotal($product,$startTime,$endTime);
        $actionType = $this->service->getType();
        $dbCount = (new UserActionLogModel())
            ->setTableNameWithMonth($startTime)
            ->whereBetween('action_time',[$startTime,$endTime])
            ->where('product_id',$product['id'])
            ->where('type',$actionType)
            ->count();

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
        $where = [
            'product_ids' => $this->getParam('product_ids'),
            'cp_type'   => $this->service->getCpType(),
            'type'      => $this->service->getType(),
        ];

        $productService = new ProductService();
        $productList = $productService->get($where);

        $startTime = $this->getParam('start_time');
        $endTime = $this->getParam('end_time');

        foreach ($productList as $product){
            $diff = $this->getDiffByProduct($product,$startTime,$endTime);
            if($diff > 0){
                echo " 相差{$diff} \n";
                $this->syncByProduct($product,$startTime,$endTime);
            }
        }
    }


}
