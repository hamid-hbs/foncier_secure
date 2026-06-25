<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id', 'sujet', 'message', 'priorite', 'statut',
        'assigned_to_id', 'reponse', 'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assigne()
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }
}
