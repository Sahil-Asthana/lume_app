<?php
  
namespace App\Mail;
  
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Task;
use App\User;
use Illuminate\Support\Facades\DB;
  
class DailyEmail extends Mailable
{
    use Queueable, SerializesModels;
  
    public $user;
  
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        
    }
  
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $notifsDue = Task::query()->whereDate('due_date', date('Y-m-d'))
                                        ->where('assignee',$this->user->id)->get();
        $notifsOverDue = Task::query()->where('due_date','<',DB::raw('CURDATE()'))
                                        ->where('assignee',$this->user->id)->get();
        return $this->subject('Your Task Update for Today '.$this->user->name)
                    ->view('emails.dailyEmails', ['overdue'=>$notifsOverDue, 'due'=>$notifsDue]);
    }
}