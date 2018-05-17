<?php

namespace ExactivEM\Console\Commands;

use Illuminate\Console\Command;

class CronJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //backup database
        $this->call('backup:run');
        $this->info('success');

        //import attendance
        file_get_contents(url('attendance/importAttendance/'. date('Y-m-d')));
    }
}
