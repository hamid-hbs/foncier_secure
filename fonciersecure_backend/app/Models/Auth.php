<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'password',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'is_active',
        'role',
        'indice_confiance',
    ];

    protected $hidden = [
        'password',
        'otp',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function professionnel()
    {
        return $this->hasOne(Professionnel::class);
    }

    public function parcelles()
    {
        return $this->hasMany(Parcelle::class, 'proprietaire_id');
    }

    public function verifications()
    {
        return $this->hasMany(Verification::class, 'demandeur_id');
    }

    public function coffreDossiers()
    {
        return $this->hasMany(CoffreDossier::class);
    }

    public function roleRequests()
    {
        return $this->hasMany(RoleRequest::class);
    }

    public function dossiersVente()
    {
        return $this->hasMany(DossierTransaction::class, 'vendeur_id');
    }

    public function dossiersAchat()
    {
        return $this->hasMany(DossierTransaction::class, 'acheteur_id');
    }

    public function dossierIntervenants()
    {
        return $this->hasMany(DossierIntervenant::class);
    }

    public function interventionsGeometre()
    {
        return $this->hasMany(InterventionGeometre::class, 'geometre_id');
    }

    public function blockchainLogs()
    {
        return $this->hasMany(BlockchainLog::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'notifiable_id')
            ->where('notifiable_type', static::class);
    }

    public function demandesAchat()
    {
        return $this->hasMany(DemandeAchat::class, 'acheteur_id');
    }

    public function dossiersNotaire()
    {
        return $this->hasMany(DossierTransaction::class, 'notaire_id');
    }

    public function verificationsIdentite()
    {
        return $this->hasMany(VerificationIdentite::class, 'user_id');
    }

    public function confirmationsRendezVous()
    {
        return $this->hasMany(ConfirmationRendezVous::class, 'user_id');
    }
}

class RoleRequest extends Model
{
    protected $table = 'role_requests';

    protected $fillable = [
        'user_id',
        'role_demande',
        'statut',
        'document_justificatif',
        'valide_par',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function valideur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}

class Professionnel extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'cabinet',
        'zone_intervention',
        'specialites',
        'note_moyenne',
    ];

    protected $casts = [
        'specialites' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function avis()
    {
        return $this->hasMany(AvisProfessionnel::class);
    }

    public function recalculerNote(): void
    {
        $this->note_moyenne = round($this->avis()->avg('note') ?? 0, 1);
        $this->save();
    }
}

class AvisProfessionnel extends Model
{
    protected $table = 'avis_professionnel';

    protected $fillable = [
        'professionnel_id',
        'auteur_id',
        'dossier_id',
        'note',
        'commentaire',
    ];

    public function professionnel()
    {
        return $this->belongsTo(Professionnel::class);
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }
}
