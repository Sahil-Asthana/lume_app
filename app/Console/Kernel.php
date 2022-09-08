<?php

namespace App\Console;
use App\Task;
use Illuminate\Support\Facades\DB;
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
        Commands\AutoDailyEmail::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->call( function() {
        //     Task::whereDate('due_date','<',DB::raw('CURDATE()'))
        //         ->where('status','deleted')
        //         ->update(['status'=>'overdue']);
        // })->everyMinute();
        $schedule->command('auto:dailyMail')->daily();
    }
    // protected function commands()
    // {
    //     $this->load(__DIR__.'/Commands');
  
    //     require base_path('routes/console.php');
    // }

}
