<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    protected $fillable = [
        'geometre_id', 'citoyen_id', 'parcelle_id', 'titre',
        'description', 'type_mission', 'statut', 'prix_estime',
        'rapport_path', 'date_soumise', 'date_acceptee', 'completed_at',
    ];

    protected $casts = [
        'prix_estime' => 'decimal:2',
        'date_soumise' => 'datetime',
        'date_acceptee' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function citoyen()
    {
        return $this->belongsTo(User::class, 'citoyen_id');
    }

    public function geometre()
    {
        return $this->belongsTo(User::class, 'geometre_id');
    }

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class);
    }

    public function rendezVous()
    {
        return $this->morphMany(RendezVous::class, 'lie');
    }

    public function messages()
    {
        return $this->morphMany(Message::class, 'messageable');
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function factures()
    {
        return $this->morphMany(Facture::class, 'facturable');
    }
}
