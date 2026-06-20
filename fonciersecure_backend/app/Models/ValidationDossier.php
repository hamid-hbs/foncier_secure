<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationDossier extends Model
{
    protected $table = 'validations_dossier';

    protected $fillable = [
        'dossier_id',
        'user_id',
        'valide_le',
    ];

    protected $casts = [
        'valide_le' => 'datetime',
    ];

    public function dossier()
    {
        return $this->belongsTo(DossierTransaction::class, 'dossier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
