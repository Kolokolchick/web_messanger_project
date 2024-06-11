<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = ['name', 'created_by'];

    /**
     * Пользователи, состоящие в группе.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Пользователь, создавший группу.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
