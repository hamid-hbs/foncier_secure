<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockchainLog extends Model
{
    protected $table = 'blockchain_logs';

    protected $fillable = [
        'previous_hash',
        'current_hash',
        'action',
        'user_id',
        'module',
        'reference_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function genererHash(array $data): string
    {
        $concatenated = implode('|', [
            $data['id'] ?? '',
            $data['previous_hash'] ?? '',
            $data['action'] ?? '',
            $data['user_id'] ?? '',
            $data['module'] ?? '',
            $data['reference_id'] ?? '',
            json_encode($data['metadata'] ?? []),
        ]);
        return hash('sha256', $concatenated);
    }

    public static function enregistrer(
        string $action,
        ?int $userId,
        ?string $module,
        ?int $referenceId,
        ?array $details = null
    ): self {
        $previous = self::orderBy('id', 'desc')->first();
        $previousHash = $previous ? $previous->current_hash : str_repeat('0', 64);

        $data = [
            'id' => null,
            'previous_hash' => $previousHash,
            'action' => $action,
            'user_id' => $userId,
            'module' => $module,
            'reference_id' => $referenceId,
            'metadata' => $details,
        ];
        $data['id'] = ($previous ? $previous->id : 0) + 1;
        $currentHash = self::genererHash($data);

        return self::create([
            'previous_hash' => $previousHash,
            'current_hash' => $currentHash,
            'action' => $action,
            'user_id' => $userId,
            'module' => $module,
            'reference_id' => $referenceId,
            'metadata' => $details,
        ]);
    }

    public static function verifierIntegrite(): array
    {
        $logs = self::orderBy('id')->get();
        $results = [];

        foreach ($logs as $i => $log) {
            $expectedPrevious = $i === 0 ? str_repeat('0', 64) : $logs[$i - 1]->current_hash;
            $hashValid = $log->previous_hash === $expectedPrevious;

            $data = [
                'id' => $log->id,
                'previous_hash' => $log->previous_hash,
                'action' => $log->action,
                'user_id' => $log->user_id,
                'module' => $log->module,
                'reference_id' => $log->reference_id,
                'metadata' => $log->metadata,
            ];
            $expectedHash = self::genererHash($data);
            $dataValid = $log->current_hash === $expectedHash;

            $results[] = [
                'id' => $log->id,
                'valid' => $hashValid && $dataValid,
                'chain_link_valid' => $hashValid,
                'data_integrity_valid' => $dataValid,
            ];
        }

        return [
            'total_blocks' => count($results),
            'all_valid' => collect($results)->every(fn($r) => $r['valid']),
            'details' => $results,
        ];
    }

    public static function historique(?string $module = null, ?int $referenceId = null)
    {
        $query = self::query();
        if ($module) {
            $query->where('module', $module);
        }
        if ($referenceId !== null) {
            $query->where('reference_id', $referenceId);
        }
        return $query->orderBy('id', 'desc')->get();
    }
}
