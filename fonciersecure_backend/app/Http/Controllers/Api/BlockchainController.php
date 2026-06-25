<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlockchainLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockchainController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(BlockchainLog::with('user')->orderBy('id', 'desc')->paginate(20));
    }

    public function verifier(): JsonResponse
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

        return response()->json([
            'valide' => $valide,
            'total_entrees' => $logs->count(),
        ]);
    }

    public function historiqueModule(string $module, ?int $referenceId = null): JsonResponse
    {
        $query = BlockchainLog::with('user')->where('module', $module);

        if ($referenceId) {
            $query->where('reference_id', $referenceId);
        }

        return response()->json($query->orderBy('id', 'desc')->get());
    }
}
