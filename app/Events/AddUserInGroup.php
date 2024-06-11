<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AddUserInGroup implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $groupName;

    public function __construct($userId, $groupName)
    {
        $this->userId = $userId;
        $this->groupName = $groupName;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('UserInGroup.' . $this->userId);
    }

    public function broadcastWith()
    {
        return [
            "AddedUserId" => $this->userId,
            "GroupName" => $this->groupName
        ];
    }
}
