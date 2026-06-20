<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    protected $table = 'rendez_vous';

    protected $fillable = [
        'dossier_id',
        'type',
        'date_prevue',
        'lieu',
        'statut',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierTransaction::class, 'dossier_id');
    }

    public function confirmations()
    {
        return $this->hasMany(ConfirmationRendezVous::class, 'rendez_vous_id');
    }
}
