<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Enums\AdvAliasEnum;
use App\Common\Enums\PlatformEnum;
use App\Common\Services\ErrorLogService;
use App\Common\Services\SystemApi\AdvBdApiService;
use App\Common\Services\SystemApi\AdvOceanApiService;
use App\Common\Services\SystemApi\UnionApiService;
use App\Common\Tools\CustomException;
use App\Common\Tools\CustomQueue;
use App\Enums\QueueEnums;


class PushChannelAdCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push_channel_ad';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '同步渠道关联计划';



    /**
     * @var
     * 书城类型
     */
    protected $cpType;

    /**
     * @var
     * 产品类型
     */
    protected $productType;


    /**
     * @var
     * 时间区间
     */
    protected $startDate,$endDate;


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }



    public function handle(){


        $this->lockRun(function (){
            $this->action();
        },'push_channel_ad',60*60*2,['log' => true]);
    }


    public function action(){
        $queue = new CustomQueue(QueueEnums::PUSH_CHANNEL_AD);
        $rePushData = [];

        while ($data = $queue->pull()){

            try{
                $channel = (new UnionApiService())->apiReadChannel(['id' => $data['channel_id']]);
                $channel['admin_id'] = $channel['channel_extends']['admin_id'] ?? 0;
                unset($channel['extends'],$channel['channel_extends']);

                if($data['adv_alias'] == AdvAliasEnum::OCEAN){
                    (new AdvOceanApiService())->apiUpdateChannelAd(
                        $data['channel_id'],
                        $data['ad_ids'],
                        PlatformEnum::DEFAULT,
                        $channel
                    );
                }elseif ($data['adv_alias'] == AdvAliasEnum::BAI_DU){
                    (new AdvBdApiService())->apiUpdateChannelAdgroup(
                        $data['channel_id'],
                        $data['ad_ids'],
                        PlatformEnum::DEFAULT,
                        $channel
                    );
                }


            }catch (CustomException $e){

                //日志
                (new ErrorLogService())->catch($e);

                $queue->item['exception'] = $e->getErrorInfo();
                $queue->item['code'] = $e->getCode();
                $rePushData[] = $queue->item;

                var_dump($e->getErrorInfo());

            }catch (\Exception $e){


                //日志
                (new ErrorLogService())->catch($e);

                $queue->item['exception'] = $e->getMessage();
                $queue->item['code'] = $e->getCode();
                $rePushData[] = $queue->item;

                var_dump($e->getMessage());
            }
        }


        // 数据重回队列
        foreach ($rePushData as $item){
            $queue->setItem($item);
            $queue->rePush();
        }

    }
}
