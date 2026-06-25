<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    protected $table = 'rendez_vous';

    protected $fillable = [
        'type', 'lie_type', 'lie_id', 'lieu', 'date_time',
        'organisateur_id', 'statut', 'tous_confirmes',
    ];

    protected $casts = [
        'date_time' => 'datetime',
        'tous_confirmes' => 'boolean',
    ];

    public function lie()
    {
        return $this->morphTo();
    }

    public function organisateur()
    {
        return $this->belongsTo(User::class, 'organisateur_id');
    }

    public function participants()
    {
        return $this->hasMany(RendezVousParticipant::class);
    }
}
