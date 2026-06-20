<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationIdentite extends Model
{
    protected $table = 'verifications_identite';

    protected $fillable = [
        'dossier_id',
        'user_id',
        'document_verifie',
        'statut',
        'verifie_par',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierTransaction::class, 'dossier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifiePar()
    {
        return $this->belongsTo(User::class, 'verifie_par');
    }
}
