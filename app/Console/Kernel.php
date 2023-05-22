<?php

namespace App\Console;


use App\Common\Enums\AdvAliasEnum;
use App\Console\Commands\CheckUserActionCommand;
use App\Console\Commands\CreateTableCommand;
use App\Console\Commands\FillUserActionChannelCommand;
use App\Console\Commands\ForwardDataCommand;
use App\Console\Commands\MatchDataToDbCommand;
use App\Console\Commands\PushAdvClickCommand;
use App\Console\Commands\SyncUserActionCommand;
use App\Console\Commands\PushUserActionCommand;
use App\Console\Commands\UserActionDataToDbCommand;
use App\Console\Commands\YgTaskCallbackCommand;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

        CreateTableCommand::class,

        // 队列行为数据入库
        UserActionDataToDbCommand::class,
        // 匹配数据入库
        MatchDataToDbCommand::class,
        // 拉取用户行为
        SyncUserActionCommand::class,
        // 补充用户行为渠道
        FillUserActionChannelCommand::class,
        // 监测用户行为数据
        CheckUserActionCommand::class,
        // 推送用户行为
        PushUserActionCommand::class,
        // 推送点击数据
        PushAdvClickCommand::class,
        // 转发数据
        ForwardDataCommand::class,

        //阳光短剧数据任务
        YgTaskCallbackCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //创建分表
        $schedule->command('create_table')->cron('0 0 1,15 * *');

        //时间范围
        $dateTime = date('Y-m-d H:i:s',TIMESTAMP);
        $fiveMinuteFront = date('Y-m-d H:i:s',TIMESTAMP-60*5);
        $tenMinuteFront = date('Y-m-d H:i:s',TIMESTAMP-60*10);
        $oneHourFront = date('Y-m-d H:i:s',TIMESTAMP-60*60);
        $twoHourFront = date('Y-m-d H:i:s',TIMESTAMP-60*60*2);

        //五分钟区间
        $fiveMinuteRange = "'{$fiveMinuteFront}','{$dateTime}'";
        //十分钟区间
        $tenMinuteRange = "'{$tenMinuteFront}','{$dateTime}'";
        //半小时区间
        $halfHourRange = "'".date('Y-m-d H:i:s',TIMESTAMP-60*30)."','{$dateTime}'";
        //一小时区间
        $oneHourRange = "'{$oneHourFront}','{$dateTime}'";
        //前5分钟区间
        $frontFiveMinuteRange = "'{$tenMinuteFront}','{$fiveMinuteFront}'";


        // 用户行为数据 start

        // 队列入库
        $schedule->command("user_action_data_to_db --enum=USER_REG_ACTION ")->cron('* * * * *');
        $schedule->command("user_action_data_to_db --enum=USER_ADD_SHORTCUT_ACTION ")->cron('* * * * *');
        $schedule->command("user_action_data_to_db --enum=USER_FOLLOW_ACTION ")->cron('* * * * *');

        // 阳光短剧数据任务
        $schedule->command("yg_task_callback ")->cron('*/2 * * * *');

        // 同步

        //  延迟5分钟 阅文上报的数据优先 主要同步关注行为
        $schedule->command("sync_user_action --action_type=REG --cp_type=YW --product_type=H5  --time={$frontFiveMinuteRange}")->cron('*/5 * * * *');

        $schedule->command("sync_user_action --action_type=REG --cp_type=TW --product_type=APP --time={$fiveMinuteRange}")->cron('*/5 * * * *');
        $schedule->command("sync_user_action --action_type=REG --cp_type=TW --product_type=KYY --time={$fiveMinuteRange}")->cron('*/5 * * * *');
        $schedule->command("sync_user_action --action_type=REG --cp_type=TW --product_type=H5 --time={$fiveMinuteRange} --time_interval=86400")->cron('*/5 * * * *');
        $schedule->command("sync_user_action --action_type=REG --cp_type=ZY --product_type=H5 --time={$fiveMinuteRange}")->cron('*/5 * * * *');
//        $schedule->command("sync_user_action --action_type=REG --cp_type=QY --product_type=H5  --time={$fiveMinuteRange}")->cron('*/5 * * * *');
//        $schedule->command("sync_user_action --action_type=REG --cp_type=FQ --product_type=KYY --time={$oneHourRange}")->cron('*/10 * * * *');
        $schedule->command("sync_user_action --action_type=REG --cp_type=BM --product_type=KYY --time={$fiveMinuteRange}")->cron('*/5 * * * *');
        $schedule->command("sync_user_action --action_type=REG --cp_type=ZY --product_type=KYY --time={$fiveMinuteRange} ")->cron('*/5 * * * *');
        $schedule->command("sync_user_action --action_type=REG --cp_type=MB --product_type=DY_MINI_PROGRAM --time={$fiveMinuteRange} ")->cron('*/5 * * * *');
        $schedule->command("sync_user_action --action_type=REG --cp_type=MB --product_type=WECHAT_MINI_PROGRAM --time={$fiveMinuteRange} ")->cron('*/5 * * * *');
        $schedule->command("sync_user_action --action_type=REG --cp_type=QR --product_type=WECHAT_MINI_PROGRAM --time={$fiveMinuteRange} ")->cron('*/5 * * * *');
        $schedule->command("sync_user_action --action_type=REG --cp_type=BMDJ --product_type=WECHAT_MINI_PROGRAM --time={$fiveMinuteRange} ")->cron('*/5 * * * *');
        $schedule->command("sync_user_action --action_type=REG --cp_type=HS --product_type=DJ_GZH --time={$fiveMinuteRange} ")->cron('*/5 * * * *');

        $schedule->command("sync_user_action --action_type=ADD_SHORTCUT --cp_type=BM --product_type=KYY --time={$fiveMinuteRange}")->cron('*/5 * * * *');

        $schedule->command("sync_user_action --action_type=ORDER --time={$tenMinuteRange}")->cron('*/5 * * * *');

        // 查漏补缺
        $tmpRange =  "'".date('Y-m-d H:i:s',TIMESTAMP - 60*60*24*2)."','{$oneHourFront}'";
        $schedule->command("check_user_action  --action_type=ORDER --time={$tmpRange}")->cron('10 * * * *');

        //补充用户行为的渠道信息等 阅文
        $tmp = "'".date('Y-m-d H:i:s',TIMESTAMP-60*20)."','".date('Y-m-d H:i:s',TIMESTAMP)."'";
        $schedule->command("fill_user_action_channel --product_type=KYY --time={$tmp}")->cron('*/10 * * * *');
        $schedule->command("fill_user_action_channel --product_type=H5 --time={$tmp}")->cron('*/10 * * * *');


        //上报
        $schedule->command("push_user_action")->cron('* * * * *');


        // 用户行为数据 end



        // 匹配数据入库
        $schedule->command("match_data_to_db --enum=OCEAN_MATCH_DATA ")->cron('* * * * *');
        $schedule->command("match_data_to_db --enum=KUAI_SHOU_MATCH_DATA ")->cron('* * * * *');


        //广告商点击数据上报
        $schedule->command("push_adv_click --adv_alias=".AdvAliasEnum::OCEAN)->cron('* * * * *');
        //$schedule->command("push_adv_click --adv_alias=".AdvAliasEnum::BD)->cron('* * * * *');
        //$schedule->command("push_adv_click --adv_alias=".AdvAliasEnum::KS)->cron('* * * * *');


        // 转发数据
        $schedule->command("forward_data")->cron('* * * * *');
    }


}
