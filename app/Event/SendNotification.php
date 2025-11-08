<?php

namespace App\Events;

use Illuminate\BroadCasting\Channel;
use Illuminate\BroadCasting\InteractWithSocket;
use Illuminate\BroadCasting\PresenceChannel;
use Illuminate\BroadCasting\PrivateChannel;
use Illuminate\Contracts\BroadCasting\ShouldBroadcast;
use Illuminate\Foundation\Event\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendNotification implements ShouldBroadcast
{
    use Dispatchable, InteractWithSocket, SerializesModels;

    public $meesage, $userID;

    public function __construct($meesage)
    {
        $this->meesage = $meesage;
    }

    public function broadCastOn()
    {
        return new Channel('notify-channel');
    }
}
