<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\CpTypeEnums;
use App\Common\Enums\ReportStatusEnum;
use App\Common\Tools\CustomQueue;
use App\Enums\QueueEnums;
use App\Enums\UserActionTypeEnum;
use App\Services\ProductService;
use App\Services\UserAction\Order\YgWeChatMiniProgramOrderService;
use App\Services\UserActionService;

class YgTaskCallbackCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'yg_task_callback';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '阳光短剧回调数据处理';


    public function handle(){

        $lockKey = 'yg_task_callback';

        //目前只有订单数据
        $this->lockRun(function () {
            $queue = new CustomQueue(QueueEnums::YG_TASK_CALLBACK_DATA);
            $productService = (new ProductService())->setMap();
            $orderService = new YgWeChatMiniProgramOrderService();
            $saveService = new UserActionService($orderService);
            $cpType = CpTypeEnums::YG;
            while ($data = $queue->pull()) {
                $xurl = $data['xurl'];
//                $dataType = $data['dataType'];
                if($xurl == '-1') continue;

                $content = file_get_contents($xurl);
                $list = explode("\r\n",trim($content));
                foreach ($list as $item){
                    try{
                        $itemData = json_decode($item,true);
                        $product = $productService->readByMap($cpType,$itemData['channelId']);
                        $saveData = [
                            'product_id' => $product['id'],
                            'status'    => ReportStatusEnum::WAITING,
                            'matcher'   => $product['matcher'],
                            'open_id'       => $itemData['userId'],
                            'cp_channel_id' => $itemData['referralId'],
                            'request_id'    => '',
                            'ip'            => '',
                            'data'          => $itemData,
                            'action_id'     => $itemData['id'],
                            'extend'        => array_merge([
                                'amount'        => intval($itemData['discount'] * 100),
                                'type'          => $orderService->getOrderType($itemData['type']),
                                'order_id'      => $itemData['id']
                            ],$orderService->filterExtendInfo($itemData)),
                        ];

                        $saveService->save(
                            array_merge($saveData,[
                                'action_time'   => date('Y-m-d H:i:s',$itemData['ctime']),
                                'type'          => UserActionTypeEnum::ORDER,
                            ])
                        );

                        // 支付订单
                        if($itemData['statusNotify'] == 1){
                            $saveService->save(
                                array_merge($saveData,[
                                    'action_time'   => date('Y-m-d H:i:s',$itemData['finishTime']),
                                    'type'          => UserActionTypeEnum::COMPLETE_ORDER,
                                ])
                            );
                        }

                    }catch (\Exception $e){

                        if($e->getCode() == 23000){
                            echo "  命中唯一索引 \n";
                            continue;
                        }
                    }
                }
            }

        },$lockKey,60*30,['log' => true]);
    }



}
