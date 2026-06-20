<?php

namespace App\Models;

use App\Helpers\EncryptionHelper;
use Illuminate\Database\Eloquent\Model;

class CoffreDossier extends Model
{
    protected $table = 'coffre_dossiers';

    protected $fillable = [
        'user_id',
        'titre',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(CoffreDocument::class, 'dossier_id');
    }
}

class CoffreDocument extends Model
{
    protected $table = 'coffre_documents';

    protected $fillable = [
        'dossier_id',
        'nom_fichier',
        'chemin_fichier',
        'hash_sha256',
        'version',
        'taille',
        'type_mime',
    ];

    public function dossier()
    {
        return $this->belongsTo(CoffreDossier::class, 'dossier_id');
    }

    public function partages()
    {
        return $this->hasMany(CoffrePartage::class, 'document_id');
    }

    public function verifierIntegrite(): bool
    {
        if (!file_exists(storage_path('app/' . $this->chemin_fichier))) {
            return false;
        }
        $decrypted = EncryptionHelper::readEncrypted($this->chemin_fichier);
        $actualHash = hash('sha256', $decrypted);
        return $actualHash === $this->hash_sha256;
    }
}

class CoffrePartage extends Model
{
    protected $table = 'coffre_partages';

    protected $fillable = [
        'document_id',
        'partage_avec',
        'token',
        'expire_le',
    ];

    public function document()
    {
        return $this->belongsTo(CoffreDocument::class, 'document_id');
    }

    public function estExpire(): bool
    {
        return $this->expire_le && now()->greaterThan($this->expire_le);
    }
}
