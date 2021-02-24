<?php

namespace App\Console;


use App\Console\Commands\MakeUserActionLogsTableCommand;
use App\Console\Commands\PullUserActionCommand;
use App\Console\Commands\PushUserActionCommand;
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

        MakeUserActionLogsTableCommand::class,

        PullUserActionCommand::class,
        PushUserActionCommand::class,


    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $timeRange = '"'.date('Y-m-d H:i:s',TIMESTAMP-60). '","'. date('Y-m-d H:i:s',TIMESTAMP).'"';

        // 拉取行为数据
        $schedule->command("pull:user_action --cp_type=YW --product_type=KYY --time_interval=60 --time={$timeRange}")->cron('* * * * *');


        // 上报行为数据
        $schedule->command("push:user_action --cp_type=YW --product_type=KYY --time_interval=60 --time={$timeRange}")->cron('* * * * *');
    }
}
