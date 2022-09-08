<?php
  
namespace App\Console\Commands;
  
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailyEmail;
use App\User;
  
class AutoDailyEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto:dailyMail';
  
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
  
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $users = User::all();
  
        if ($users->count() > 0) {
            foreach ($users as $user) {
                Mail::to($user)->send(new DailyEmail($user));
            }
        }
  
        return 0;
    }
}