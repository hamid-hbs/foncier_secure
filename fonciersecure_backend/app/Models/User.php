<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable;

    protected $fillable = [
        'nom', 'prenom', 'email', 'telephone', 'password_hash',
        'photo_profil', 'piece_identite_path', 'type_piece_identite',
        'role_id',
        'email_verified_at', 'phone_verified_at',
        'is_active', 'indice_confiance', 'two_factor_enabled',
        'date_inscription', 'last_login',
    ];

    protected $hidden = ['password_hash', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'date_inscription' => 'datetime',
            'last_login' => 'datetime',
            'password_hash' => 'hashed',
        ];
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $nom): bool
    {
        return $this->role?->nom === $nom;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function professionnel()
    {
        return $this->hasOne(Professionnel::class);
    }

    public function proprietes()
    {
        return $this->hasMany(Propriete::class, 'user_id');
    }

    public function demandesAchat()
    {
        return $this->hasMany(DemandeAchat::class, 'acheteur_id');
    }

    public function dossiersVente()
    {
        return $this->hasMany(DossierTransaction::class, 'vendeur_id');
    }

    public function dossiersAchat()
    {
        return $this->hasMany(DossierTransaction::class, 'acheteur_id');
    }

    public function dossiersNotaire()
    {
        return $this->hasMany(DossierTransaction::class, 'notaire_id');
    }

    public function missions()
    {
        return $this->hasMany(Mission::class, 'citoyen_id');
    }

    public function missionsGeometre()
    {
        return $this->hasMany(Mission::class, 'geometre_id');
    }

    public function rendezVousParticipants()
    {
        return $this->hasMany(RendezVousParticipant::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }

    public function blockchainLogs()
    {
        return $this->hasMany(BlockchainLog::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function messagesEnvoyes()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
}
