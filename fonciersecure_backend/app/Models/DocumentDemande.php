<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentDemande extends Model
{
    protected $table = 'documents_demande';

    protected $fillable = [
        'demande_id',
        'type_document',
        'nom_fichier',
        'chemin_fichier',
        'hash_sha256',
        'taille',
        'uploaded_at',
    ];

    public function demande()
    {
        return $this->belongsTo(DemandeAchat::class, 'demande_id');
    }
}
