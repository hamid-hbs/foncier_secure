<?php

namespace App\Services;

use App\Models\BlockchainLog;

class BlockchainService
{
    public function log(
        string $actionType,
        ?int $userId,
        ?string $module = null,
        ?int $referenceId = null,
        ?array $details = null
    ): BlockchainLog {
        return BlockchainLog::enregistrer($actionType, $userId, $module, $referenceId, $details);
    }

    public function verifierIntegrite(): array
    {
        return BlockchainLog::verifierIntegrite();
    }

    public function historique(?string $module = null, ?int $referenceId = null)
    {
        return BlockchainLog::historique($module, $referenceId);
    }
}
