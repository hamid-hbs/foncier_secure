<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arrondissement extends Model
{
    protected $fillable = ['code_arrondissement', 'nom', 'commune_id'];

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function quartiers()
    {
        return $this->hasMany(Quartier::class);
    }
}
