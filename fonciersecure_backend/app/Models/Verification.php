<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Verification extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parcelle_id',
        'demandeur_id',
        'titre',
        'statut',
        'score_risque',
        'niveau_risque',
        'rapport_path',
    ];

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class);
    }

    public function demandeur()
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function documents()
    {
        return $this->hasMany(DocumentVerification::class);
    }

    public function analyses()
    {
        return $this->hasMany(AnalyseAutomatique::class);
    }

    public function intervention()
    {
        return $this->hasOne(InterventionGeometre::class);
    }
}

class DocumentVerification extends Model
{
    protected $table = 'documents_verification';

    protected $fillable = [
        'verification_id',
        'type_document',
        'nom_fichier',
        'chemin_fichier',
        'hash_sha256',
        'taille',
        'uploaded_at',
    ];

    public function verification()
    {
        return $this->belongsTo(Verification::class);
    }

    public function verifierIntegrite(): bool
    {
        if (!file_exists(storage_path('app/' . $this->chemin_fichier))) {
            return false;
        }
        $decrypted = EncryptionHelper::readEncrypted($this->chemin_fichier);
        $actualHash = hash('sha256', $decrypted);
        return $actualHash === $this->hash_sha256;
    }
}

class AnalyseAutomatique extends Model
{
    protected $table = 'analyses_automatiques';

    protected $fillable = [
        'verification_id',
        'type_analyse',
        'resultat',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function verification()
    {
        return $this->belongsTo(Verification::class);
    }
}

class InterventionGeometre extends Model
{
    protected $table = 'interventions_geometre';

    protected $fillable = [
        'verification_id',
        'dossier_id',
        'geometre_id',
        'mission',
        'assigne_par',
        'statut',
        'rapport_path',
        'avis',
        'commentaire',
        'completed_at',
    ];

    public function verification()
    {
        return $this->belongsTo(Verification::class);
    }

    public function dossier()
    {
        return $this->belongsTo(DossierTransaction::class, 'dossier_id');
    }

    public function geometre()
    {
        return $this->belongsTo(User::class, 'geometre_id');
    }

    public function assigneur()
    {
        return $this->belongsTo(User::class, 'assigne_par');
    }
}
