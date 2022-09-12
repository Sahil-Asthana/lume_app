<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class NotificationEvent extends Event /*implements ShouldBroadcast*/
{
  use  InteractsWithSockets, SerializesModels;

  public $user;
  public $task;
  public $type;

  public function __construct($user, $task, $type)
  {
      $this->user = $user;
      $this->task = $task;
      $this->type = $type;
  }

//   public function broadcastOn()
//   {
//       return ['my-channel'];
//   }

//   public function broadcastAs()
//   {
//       return 'my-event';
//   }
}