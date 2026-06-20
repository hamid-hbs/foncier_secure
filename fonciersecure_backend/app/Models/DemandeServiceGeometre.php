<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeServiceGeometre extends Model
{
    protected $table = 'demande_services_geometre';

    protected $fillable = [
        'citoyen_id',
        'geometre_id',
        'parcelle_id',
        'titre',
        'description',
        'statut',
        'rapport_path',
        'commentaire_geometre',
        'completed_at',
    ];

    protected $casts = [
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
}
