<?php

namespace App\Listeners;

use App\Events\NotificationEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\NotificationController;

class NotificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ExampleEvent  $event
     * @return void
     */
    public function handle(NotificationEvent $event)
    {
        $content='';
        $title ='';
        switch($event->type){
            case "created":
                {$content = 'New task is created with title: '.$event->task->title;
                $title = 'New Task Assigned to '.$event->user->name;
                break;}
            case "updated":
                {$content =  'Status has been changed for task with title: '.$event->task->title;
                $title = 'Status Updated';
                break;}
            case 'edited':
                {$content =  $event->task->title.' is updated by '.$event->user->name;
                $title = 'Task is Redefined';
                break;}
            case 'deleted':
                {$content = $event->task->title.' is deleted by '.$event->user->name;
                $title = 'Task Deleted';
                break;}
            default:
                $content = 'Nothing happened' ;
            
        }
        $notif_data = ['notification'=>$content, 'user_id'=> $event->user->id];
        NotificationController::create($notif_data);
        Mail::raw($content, function ($message) use($event,$title) {
            $message->to($event->user->email)
              ->subject($title);
          });
    }
}
