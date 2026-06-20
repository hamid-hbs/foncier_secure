<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DossierTransaction extends Model
{
    protected $table = 'dossiers_transaction';

    protected $fillable = [
        'vendeur_id',
        'acheteur_id',
        'notaire_id',
        'parcelle_id',
        'titre',
        'statut',
        'motif_suspension',
        'closed_at',
    ];

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

    public function intervenants()
    {
        return $this->hasMany(DossierIntervenant::class, 'dossier_id');
    }

    public function documents()
    {
        return $this->hasMany(DossierDocument::class, 'dossier_id');
    }

    public function activites()
    {
        return $this->hasMany(DossierActivite::class, 'dossier_id');
    }

    public function messages()
    {
        return $this->hasMany(DossierMessage::class, 'dossier_id');
    }

    public function verificationsIdentite()
    {
        return $this->hasMany(VerificationIdentite::class, 'dossier_id');
    }

    public function rendezVous()
    {
        return $this->hasMany(RendezVous::class, 'dossier_id');
    }

    public function assignationsGeometre()
    {
        return $this->hasMany(InterventionGeometre::class, 'dossier_id');
    }

    public function validations()
    {
        return $this->hasMany(ValidationDossier::class, 'dossier_id');
    }

    public function factures()
    {
        return $this->hasMany(Facture::class, 'dossier_id');
    }

    public function estCloture(): bool
    {
        return $this->statut === 'cloture';
    }

    public function estEnAttente(): bool
    {
        return $this->statut === 'en_attente';
    }

    public function estValideParVendeur(): bool
    {
        return $this->validations()->where('user_id', $this->vendeur_id)->exists();
    }

    public function estValideParAcheteur(): bool
    {
        return $this->acheteur_id && $this->validations()->where('user_id', $this->acheteur_id)->exists();
    }

    public function estValideParLesDeux(): bool
    {
        return $this->estValideParVendeur() && $this->estValideParAcheteur();
    }
}

class DossierDocument extends Model
{
    protected $table = 'dossier_documents';

    protected $fillable = [
        'dossier_id',
        'uploaded_by',
        'nom_fichier',
        'chemin_fichier',
        'hash_sha256',
        'type_document',
        'version',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierTransaction::class, 'dossier_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

class DossierIntervenant extends Model
{
    protected $table = 'dossier_intervenants';

    protected $fillable = [
        'dossier_id',
        'user_id',
        'role_dossier',
        'invite_par',
        'accepted_at',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierTransaction::class, 'dossier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function inviteur()
    {
        return $this->belongsTo(User::class, 'invite_par');
    }
}

class DossierMessage extends Model
{
    protected $table = 'dossier_messages';

    protected $fillable = [
        'dossier_id',
        'sender_id',
        'contenu',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierTransaction::class, 'dossier_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}

class DossierActivite extends Model
{
    protected $table = 'dossier_activites';

    protected $fillable = [
        'dossier_id',
        'user_id',
        'action',
        'details',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function dossier()
    {
        return $this->belongsTo(DossierTransaction::class, 'dossier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
