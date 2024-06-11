<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\GroupMessage;
use Illuminate\Support\Facades\Log;
use App\Models\Group;


class MessageDeletedGroup implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public GroupMessage $message;

    public function __construct(GroupMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('MessageDeletedGroup.' . $this->message->group_id);
    }

    public function broadcastWith()
    {
        return ['messageId' => $this->message->id];
    }
}
