<?php

namespace App\Events;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PusherEvent implements ShouldBroadcast
{
  use  InteractsWithSockets, SerializesModels;

  public $message;
  public $user;

  public function __construct($message,$user)
  {
      $this->message = $message;
      $this->user = $user;
  }

  public function broadcastOn()
  {
      return ['my-channel-'.$this->user->id];
  }

  public function broadcastAs()
  {
      return 'my-event';
  }
}