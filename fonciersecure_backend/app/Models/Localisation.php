<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $fillable = ['nom'];

    public function arrondissements()
    {
        return $this->hasMany(Arrondissement::class);
    }

    public function parcelles()
    {
        return $this->hasMany(Parcelle::class);
    }
}

class Arrondissement extends Model
{
    protected $fillable = ['nom', 'commune_id'];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function quartiers()
    {
        return $this->hasMany(Quartier::class);
    }

    public function parcelles()
    {
        return $this->hasMany(Parcelle::class);
    }
}

class Quartier extends Model
{
    protected $fillable = ['nom', 'arrondissement_id'];

    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class);
    }

    public function parcelles()
    {
        return $this->hasMany(Parcelle::class);
    }
}
