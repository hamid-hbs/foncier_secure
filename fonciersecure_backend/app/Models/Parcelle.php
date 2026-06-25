<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parcelle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code_parcelle', 'titre_parcelle', 'superficie',
        'commune_id', 'arrondissement_id', 'quartier_id',
        'localisation_textuelle', 'latitude', 'longitude',
        'precision_gps', 'statut', 'type_acquisition', 'valeur_estimee',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'precision_gps' => 'decimal:8',
        'valeur_estimee' => 'decimal:2',
    ];

    public function proprietaireActuel()
    {
        return $this->hasOne(Propriete::class)->whereNull('date_fin');
    }

    public function proprietes()
    {
        return $this->hasMany(Propriete::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class);
    }

    public function quartier()
    {
        return $this->belongsTo(Quartier::class);
    }

    public function demandesAchat()
    {
        return $this->hasMany(DemandeAchat::class);
    }

    public function dossiersTransaction()
    {
        return $this->hasMany(DossierTransaction::class);
    }

    public function missions()
    {
        return $this->hasMany(Mission::class);
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
