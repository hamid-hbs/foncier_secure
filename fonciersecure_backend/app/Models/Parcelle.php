<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Parcelle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'proprietaire_id', 'code', 'titre', 'description',
        'commune_id', 'arrondissement_id', 'quartier_id',
        'superficie', 'latitude', 'longitude',
        'prix_estimatif', 'statut',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Parcelle $parcelle) {
            if (!$parcelle->code) {
                $maxId = static::max('id') ?? 0;
                $parcelle->code = 'FS-' . str_pad($maxId + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function proprietaire()
    {
        return $this->belongsTo(User::class, 'proprietaire_id');
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

    public function documents()
    {
        return $this->hasMany(ParcelleDocument::class);
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class);
    }

    public function dossiersTransaction()
    {
        return $this->hasMany(DossierTransaction::class);
    }

    public function demandesAchat()
    {
        return $this->hasMany(DemandeAchat::class);
    }
}

class ParcelleDocument extends Model
{
    protected $table = 'parcelle_documents';

    protected $fillable = [
        'parcelle_id', 'type_document',
        'nom_fichier', 'chemin_fichier',
        'hash_sha256', 'taille', 'uploaded_at',
    ];

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class);
    }
}
