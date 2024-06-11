<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    protected $fillable = [
        'name', 'email', 'password', 'profile_image',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Связь с полученными сообщениями
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'to');
    }

    // Связь с отправленными сообщениями
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'from');
    }

    public function sentMessagesToGroup()
    {
        return $this->hasMany(GroupMessage::class, 'from');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'LIKE', "%{$term}%")
            ->orWhere('email', 'LIKE', "%{$term}%");
    }

    public function contacts()
    {
        return $this->belongsToMany(User::class, 'contacts', 'user_id', 'contact_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }
}
