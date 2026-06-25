<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quartier extends Model
{
    protected $fillable = ['nom', 'arrondissement_id', 'latitude', 'longitude'];

    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class);
    }
}
