<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $fillable = ['code_commune', 'nom', 'departement', 'latitude', 'longitude'];

    public function arrondissements()
    {
        return $this->hasMany(Arrondissement::class);
    }
}
