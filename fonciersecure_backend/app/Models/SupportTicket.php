<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id', 'sujet', 'message', 'statut', 'priorite',
        'assigned_to', 'reponse', 'closed_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assigne()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
