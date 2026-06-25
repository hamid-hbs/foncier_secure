<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'documentable_type', 'documentable_id', 'type_document',
        'nom_original', 'chemin_fichier', 'hash_sha256',
        'taille_bytes', 'mime_type', 'proprietaire_nom',
        'proprietaire_prenoms', 'numero_titre', 'date_document',
        'est_verifiee', 'commentaires_verification', 'uploaded_by_id',
    ];

    protected $casts = [
        'taille_bytes' => 'integer',
        'est_verifiee' => 'boolean',
        'date_document' => 'date',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
