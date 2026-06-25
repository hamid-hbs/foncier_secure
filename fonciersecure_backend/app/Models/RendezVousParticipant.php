<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendezVousParticipant extends Model
{
    protected $table = 'rendez_vous_participants';

    protected $fillable = [
        'rendez_vous_id', 'user_id', 'est_confirme', 'date_confirmation',
    ];

    protected $casts = [
        'est_confirme' => 'boolean',
        'date_confirmation' => 'datetime',
    ];

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
