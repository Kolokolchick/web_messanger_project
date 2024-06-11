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
use Illuminate\Support\Facades\Crypt;

class MessageEditedGroup implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public GroupMessage $message;

    public function __construct(GroupMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new Channel('MessageEditedGroup.' . $this->message->group_id);
    }

    public function broadcastWith()
    {
        // Расшифровываем текст перед отправкой улиенту
        $this->message->text = Crypt::decryptString($this->message->text);

        return ['message' => $this->message];
    }
}
