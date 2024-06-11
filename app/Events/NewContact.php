<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewContact implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $contact;
    private $receiverId;

    public function __construct(User $contact, $receiverId)
    {
        $this->contact = $contact;
        $this->receiverId = $receiverId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('contacts.' . $this->receiverId);
    }

    public function broadcastWith()
    {
        return ["contact" => $this->contact];
    }
}
