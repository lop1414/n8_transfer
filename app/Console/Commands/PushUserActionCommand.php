<?php

namespace App\Console\Commands;

use App\Common\Console\BaseCommand;
use App\Common\Helpers\Functions;
use App\Enums\UserActionTypeEnum;
use App\Services\PushUserActionService;

class PushUserActionCommand extends BaseCommand
{
    /**
     * 命令行执行命令
     * @var string
     */
    protected $signature = 'push_user_action {--action_type=} {--product_id=}';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '推送用户行为数据';



    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(){
        parent::__construct();
    }



    public function handle(){
        $productId = $this->option('product_id');
        $actionType = $this->option('action_type');
        if(!empty($this->actionType)){
            Functions::hasEnum(UserActionTypeEnum::class, $actionType);
        }


        $this->lockRun(function () use ($actionType,$productId){

            (new PushUserActionService())->push($actionType,$productId);

        },"push|{$actionType}",60*60,['log' => true]);

    }

}
