<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $fillable = [
        'reference', 'facturable_type', 'facturable_id',
        'prestataire_id', 'client_id', 'montant_total', 'montant_paye',
        'description', 'statut', 'date_emission', 'date_echeance',
        'date_paiement', 'moyen_paiement',
    ];

    protected $casts = [
        'montant_total' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'date_paiement' => 'datetime',
    ];

    public function facturable()
    {
        return $this->morphTo();
    }

    public function prestataire()
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
