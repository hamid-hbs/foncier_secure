<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeAchat extends Model
{
    protected $table = 'demandes_achat';

    protected $fillable = [
        'parcelle_id',
        'acheteur_id',
        'message',
        'statut',
        'notaire_id',
    ];

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class);
    }

    public function acheteur()
    {
        return $this->belongsTo(User::class, 'acheteur_id');
    }

    public function notaire()
    {
        return $this->belongsTo(User::class, 'notaire_id');
    }

    public function documents()
    {
        return $this->hasMany(DocumentDemande::class, 'demande_id');
    }

    public function messages()
    {
        return $this->hasMany(DemandeMessage::class, 'demande_id');
    }
}

class DemandeMessage extends Model
{
    protected $table = 'demande_messages';

    protected $fillable = [
        'demande_id',
        'sender_id',
        'contenu',
    ];

    public function demande()
    {
        return $this->belongsTo(DemandeAchat::class, 'demande_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
