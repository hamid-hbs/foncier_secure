<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analyse extends Model
{
    protected $fillable = [
        'analysable_type', 'analysable_id', 'type_analyse',
        'score', 'niveau_risque', 'resultats', 'recommandations',
        'analyseur',
    ];

    protected $casts = [
        'score' => 'integer',
        'resultats' => 'array',
        'recommandations' => 'array',
    ];

    public function analysable()
    {
        return $this->morphTo();
    }
}
