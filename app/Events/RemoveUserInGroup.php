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

class RemoveUserInGroup implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $groupId;
    public $groupName;

    public function __construct($userId, $groupId, $groupName)
    {
        $this->userId = $userId;
        $this->groupId = $groupId;
        $this->groupId = intval($this->groupId );
        $this->groupName = $groupName;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('UserInGroup.' . $this->userId);
    }

    public function broadcastWith()
    {
        return [
            "RemovedUserId" => $this->userId,
            "GroupId" => $this->groupId,
            "GroupName" => $this->groupName
        ];
    }
}
