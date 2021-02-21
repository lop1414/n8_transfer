<?php

namespace App\Console;

use App\Console\Commands\AnalogPushCommand;
use App\Console\Commands\MakeUserActionLogsTableCommand;
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
        AnalogPushCommand::class
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
        $schedule->command("analog_push --time={$timeRange}")->cron('* * * * *');

    }
}
