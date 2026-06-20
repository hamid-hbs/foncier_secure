<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    protected $fillable = [
        'dossier_id',
        'emetteur_id',
        'reference',
        'montant',
        'description',
        'statut',
        'envoyee_le',
        'payee_le',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'envoyee_le' => 'datetime',
        'payee_le' => 'datetime',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierTransaction::class, 'dossier_id');
    }

    public function emetteur()
    {
        return $this->belongsTo(User::class, 'emetteur_id');
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Facture $facture) {
            if (!$facture->reference) {
                $maxId = static::max('id') ?? 0;
                $facture->reference = 'FACT-' . str_pad($maxId + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
