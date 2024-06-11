<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    protected $fillable = [
        'from', 'group_id', 'text', 'from_name'
    ];

    // Связь с отправляющим пользователем
    public function sender()
    {
        return $this->belongsTo(User::class, 'from');
    }

    // Связь с получающей группой
    public function recipient()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
