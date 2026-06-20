<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BlockchainService;
use Illuminate\Http\JsonResponse;

class BlockchainController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = app(BlockchainService::class)->historique();
        return response()->json($logs);
    }

    public function verifier(): JsonResponse
    {
        $resultat = app(BlockchainService::class)->verifierIntegrite();
        return response()->json($resultat);
    }

    public function historiqueModule(string $module, ?int $referenceId = null): JsonResponse
    {
        $logs = app(BlockchainService::class)->historique($module, $referenceId);
        return response()->json($logs);
    }
}
