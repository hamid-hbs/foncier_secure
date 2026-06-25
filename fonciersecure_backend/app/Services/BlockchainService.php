<?php

namespace App\Services;

use App\Models\BlockchainLog;
use Illuminate\Support\Str;

class BlockchainService
{
    public function log(
        string $actionType,
        ?int $userId,
        ?string $module = null,
        ?int $referenceId = null,
        ?array $details = null
    ): BlockchainLog {
        $previous = BlockchainLog::orderBy('id', 'desc')->first();
        $previousHash = $previous ? $previous->current_hash : str_repeat('0', 64);

        $data = json_encode([
            'action' => $actionType,
            'user_id' => $userId,
            'module' => $module,
            'reference_id' => $referenceId,
            'details' => $details,
            'previous_hash' => $previousHash,
            'timestamp' => now()->toIso8601String(),
        ]);

        return BlockchainLog::create([
            'previous_hash' => $previousHash,
            'current_hash' => hash('sha256', $data),
            'action' => $actionType,
            'user_id' => $userId,
            'module' => $module,
            'reference_id' => $referenceId,
            'metadata' => $details,
        ]);
    }

    public function verifierIntegrite(): array
    {
        $logs = BlockchainLog::orderBy('id', 'asc')->get();
        $valide = true;
        $previousHash = str_repeat('0', 64);

        foreach ($logs as $log) {
            if ($log->previous_hash !== $previousHash) {
                $valide = false;
                break;
            }
            $previousHash = $log->current_hash;
        }

        return [
            'valide' => $valide,
            'total_entrees' => $logs->count(),
        ];
    }

    public function historique(?string $module = null, ?int $referenceId = null)
    {
        $query = BlockchainLog::with('user');

        if ($module) {
            $query->where('module', $module);
        }

        if ($referenceId !== null) {
            $query->where('reference_id', $referenceId);
        }

        return $query->orderBy('id', 'desc')->get();
    }
}
