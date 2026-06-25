<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Propriete extends Model
{
    protected $fillable = [
        'user_id', 'parcelle_id', 'date_debut', 'date_fin',
        'type_propriete', 'part_indivision', 'statut',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'part_indivision' => 'decimal:2',
    ];

    public function parcelle()
    {
        return $this->belongsTo(Parcelle::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
