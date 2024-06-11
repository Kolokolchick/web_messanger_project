<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'from', 'to', 'text', 'read'
    ];

    // Связь с отправляющим пользователем
    public function sender()
    {
        return $this->belongsTo(User::class, 'from');
    }

    // Связь с получающим пользователем
    public function recipient()
    {
        return $this->belongsTo(User::class, 'to');
    }

    public function fromContact()
    {
        return $this->belongsTo('App\Models\User', 'contact_id', 'id');
    }
}

