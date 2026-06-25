<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'sender_id', 'receiver_id', 'messageable_type',
        'messageable_id', 'contenu', 'est_lu', 'date_lu',
    ];

    protected $casts = [
        'est_lu' => 'boolean',
        'date_lu' => 'datetime',
    ];

    public function messageable()
    {
        return $this->morphTo();
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
