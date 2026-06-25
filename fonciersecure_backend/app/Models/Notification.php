<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'notifiable_type', 'notifiable_id', 'titre',
        'message', 'type_action', 'lien_redirection', 'est_lue', 'date_lu',
    ];

    protected $casts = [
        'est_lue' => 'boolean',
        'date_lu' => 'datetime',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
