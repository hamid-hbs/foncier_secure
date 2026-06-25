<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvisProfessionnel extends Model
{
    protected $fillable = [
        'professionnel_id', 'citoyen_id', 'note', 'commentaire',
        'date_avis', 'dossier_transaction_id',
    ];

    protected $casts = [
        'date_avis' => 'date',
    ];

    public function professionnel()
    {
        return $this->belongsTo(Professionnel::class);
    }

    public function citoyen()
    {
        return $this->belongsTo(User::class, 'citoyen_id');
    }

    public function dossierTransaction()
    {
        return $this->belongsTo(DossierTransaction::class);
    }
}
