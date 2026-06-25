<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandeAchat extends Model
{
    protected $table = 'demande_achat';
    public $timestamps = false;

    use SoftDeletes;

    protected $fillable = [
        'parcelle_id', 'acheteur_id', 'vendeur_id', 'statut',
        'message_acheteur', 'code_secret', 'notaire_id', 'raison_refus',
        'tentatives_approbation', 'date_soumise', 'date_acceptee',
        'date_refusee', 'dossier_transaction_id',
    ];

    protected $casts = [
        'tentatives_approbation' => 'integer',
        'date_soumise' => 'datetime',
        'date_acceptee' => 'datetime',
        'date_refusee' => 'datetime',
    ];

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class);
    }

    public function acheteur()
    {
        return $this->belongsTo(User::class, 'acheteur_id');
    }

    public function vendeur()
    {
        return $this->belongsTo(User::class, 'vendeur_id');
    }

    public function notaire()
    {
        return $this->belongsTo(User::class, 'notaire_id');
    }

    public function dossierTransaction()
    {
        return $this->belongsTo(DossierTransaction::class);
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
