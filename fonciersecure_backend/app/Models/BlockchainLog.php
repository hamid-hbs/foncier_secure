<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainLog extends Model
{
    protected $fillable = [
        'module', 'reference_id', 'action', 'donnees_hash_inputs',
        'hash_courant', 'hash_precedent', 'acteur_id', 'sequence',
        'est_verifie',
    ];

    protected $casts = [
        'donnees_hash_inputs' => 'array',
        'est_verifie' => 'boolean',
        'sequence' => 'integer',
    ];

    public function acteur()
    {
        return $this->belongsTo(User::class, 'acteur_id');
    }
}
