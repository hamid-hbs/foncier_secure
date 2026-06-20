<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfirmationRendezVous extends Model
{
    protected $table = 'confirmations_rendez_vous';

    protected $fillable = [
        'rendez_vous_id',
        'user_id',
        'est_confirme',
    ];

    public function rendezVous()
    {
        return $this->belongsTo(RendezVous::class, 'rendez_vous_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
