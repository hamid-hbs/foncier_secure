<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Professionnel extends Model
{
    protected $fillable = [
        'user_id', 'type', 'numero_enregistrement', 'date_enregistrement',
        'date_expiration', 'specialisation', 'commune_id', 'adresse_bureau',
        'taux_horaire', 'note_moyenne', 'nombre_avis', 'is_verified',
    ];

    protected $casts = [
        'date_enregistrement' => 'date',
        'date_expiration' => 'date',
        'taux_horaire' => 'decimal:2',
        'note_moyenne' => 'decimal:2',
        'nombre_avis' => 'integer',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function avis()
    {
        return $this->hasMany(AvisProfessionnel::class);
    }

    public function recalculerNote(): void
    {
        $this->note_moyenne = round($this->avis()->avg('note') ?? 0, 1);
        $this->nombre_avis = $this->avis()->count();
        $this->save();
    }
}
