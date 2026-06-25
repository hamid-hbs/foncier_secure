<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DossierTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'demande_achat_id', 'notaire_id', 'geometre_assigne_id',
        'vendeur_id', 'acheteur_id', 'parcelle_id', 'statut',
        'motif_suspension', 'vendeur_valide', 'acheteur_valide',
        'date_validation_vendeur', 'date_validation_acheteur',
        'date_actif', 'date_signature_effective', 'prix_vente',
        'frais_enregistrement', 'acte_notarie_path',
        'numero_enregistrement', 'date_cloture',
    ];

    protected $casts = [
        'vendeur_valide' => 'boolean',
        'acheteur_valide' => 'boolean',
        'date_validation_vendeur' => 'datetime',
        'date_validation_acheteur' => 'datetime',
        'date_actif' => 'datetime',
        'date_signature_effective' => 'datetime',
        'prix_vente' => 'decimal:2',
        'frais_enregistrement' => 'decimal:2',
        'date_cloture' => 'datetime',
    ];

    public function demandeAchat()
    {
        return $this->belongsTo(DemandeAchat::class);
    }

    public function geometreAssigne()
    {
        return $this->belongsTo(User::class, 'geometre_assigne_id');
    }

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class);
    }

    public function notaire()
    {
        return $this->belongsTo(User::class, 'notaire_id');
    }

    public function vendeur()
    {
        return $this->belongsTo(User::class, 'vendeur_id');
    }

    public function acheteur()
    {
        return $this->belongsTo(User::class, 'acheteur_id');
    }

    public function rendezVous()
    {
        return $this->morphMany(RendezVous::class, 'lie');
    }

    public function factures()
    {
        return $this->morphMany(Facture::class, 'facturable');
    }

    public function avis()
    {
        return $this->hasMany(AvisProfessionnel::class);
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'messageable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function analyses()
    {
        return $this->morphMany(Analyse::class, 'analysable');
    }
}
