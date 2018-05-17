<?php

namespace ExactivEM\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use ExactivEM\Config;
use ExactivEM\User;
use ExactivEM\Branch;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
        Commands\CronJobs::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            file_get_contents('https://'.Config::find(23)->value.'/mysql/killProcesses');
        })->everyFiveMinutes()->timezone('Asia/Manila');

        $schedule->call(function () {
            file_get_contents('https://'.Config::find(23)->value.'/fingerprint/collectLogs/all');
        })->cron('14,32 * * * * *')->timezone('Asia/Manila');

        $schedule->call(function () {
            file_get_contents('https://'.Config::find(23)->value.'/attendance/importAttendance/'. date('Y-m-d') ."/all" );
        })->cron('17,42 * * * * *')->timezone('Asia/Manila');

        $schedule->call(function () {
            file_get_contents('https://'.Config::find(23)->value.'/fingerprint/convertToJSON');
        })->cron('47 * * * * *')->timezone('Asia/Manila');

        $schedule->call(function () {
            file_get_contents('https://'.Config::find(23)->value.'/fingerprint/createMasterFile');
        })->cron('51 * * * * *')->timezone('Asia/Manila');


        //birthday daily greet
        $schedule->call(function () {
            file_get_contents('https://'.Config::find(23)->value.'/birthday/broadcastCelebrant/'.date('m').'/'.date('d'));
        })->dailyAt('08:00')->timezone('Asia/Manila');

        //birthday monthly
        $schedule->call(function () {
            //file_get_contents('https://'.Config::find(23)->value.'/birthday/broadcastCelebrant/'.date('m'));
        })->monthly()->timezone('Asia/Manila');

        //notification create daily
        $schedule->call(function () {
            file_get_contents('https://'.Config::find(23)->value.'/notifications/generateNotifications/'.date('Y-m-d'));
        })->dailyAt('14:49')->timezone('Asia/Manila');

        //notification send daily
        $schedule->call(function () {
            file_get_contents('https://'.Config::find(23)->value.'/notifications/sendAbsentNotification/'.date('Y-m-d'));
        })->dailyAt('14:25')->timezone('Asia/Manila');

        //cleaner
        $schedule->call(function () {
            file_get_contents('https://'.Config::find(23)->value.'/notifications/cleanNotifications');
        })->hourly()->timezone('Asia/Manila');

    }
}