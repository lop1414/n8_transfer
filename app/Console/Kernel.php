<?php

namespace App\Console;

use App\Console\Commands\AnalogPush\TwKyyCommand;
use App\Console\Commands\AnalogPush\YwKyyCommand;
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
        //
        YwKyyCommand::class,
        TwKyyCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {


        $timeRange = '"'.date('Y-m-d H:i:s',TIMESTAMP). '","'. date('Y-m-d H:i:s',TIMESTAMP-60).'"';
        $schedule->command("analog_push:yw_kyy --time={$timeRange}")->cron('* * * * *');
        $schedule->command("analog_push:tw_kyy --time={$timeRange}")->cron('* * * * *');

    }
}
