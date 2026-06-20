<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Arrondissement;
use App\Models\BlockchainLog;
use App\Models\Commune;
use App\Models\Professionnel;
use App\Models\Quartier;
use App\Models\RoleRequest;
use App\Models\User;
use App\Services\BlockchainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'total_users' => User::count(),
            'total_citoyens' => User::where('role', 'citoyen')->count(),
            'total_agents' => 0,
            'total_geometres' => User::where('role', 'geometre')->count(),
            'total_notaires' => User::where('role', 'notaire')->count(),
            'pending_role_requests' => RoleRequest::where('statut', 'en_attente')->count(),
            'recent_users' => User::latest()->take(10)->get(),
        ]);
    }

    public function users(): JsonResponse
    {
        $users = User::with('professionnel')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($users);
    }

    public function toggleUserStatus(User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        return response()->json($user);
    }

    public function roleRequests(): JsonResponse
    {
        $requests = RoleRequest::with(['user', 'valideur'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($requests);
    }

    public function approveRoleRequest(Request $request, RoleRequest $roleRequest): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:valide,rejete',
        ]);

        $roleRequest->update([
            'statut' => $validated['action'],
            'valide_par' => $request->user()->id,
        ]);

        if ($validated['action'] === 'valide') {
            $roleRequest->user->update(['role' => $roleRequest->role_demande]);
        }

        app(BlockchainService::class)->log(
            'role_request_' . $validated['action'],
            $request->user()->id,
            'admin',
            $roleRequest->id,
            ['user_id' => $roleRequest->user_id, 'role' => $roleRequest->role_demande]
        );

        return response()->json($roleRequest);
    }

    public function createCommune(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
        ]);

        $commune = Commune::create($validated);

        return response()->json($commune, 201);
    }

    public function updateCommune(Request $request, Commune $commune): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
        ]);

        $commune->update($validated);

        return response()->json($commune);
    }

    public function deleteCommune(Commune $commune): JsonResponse
    {
        $commune->load('arrondissements.quartiers');

        foreach ($commune->arrondissements as $arrondissement) {
            $arrondissement->quartiers()->delete();
        }

        $commune->arrondissements()->delete();
        $commune->delete();

        return response()->json(null, 204);
    }

    public function createArrondissement(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'commune_id' => 'required|exists:communes,id',
        ]);

        $arrondissement = Arrondissement::create($validated);

        return response()->json($arrondissement, 201);
    }

    public function updateArrondissement(Request $request, Arrondissement $arrondissement): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'commune_id' => 'required|exists:communes,id',
        ]);

        $arrondissement->update($validated);

        return response()->json($arrondissement);
    }

    public function deleteArrondissement(Arrondissement $arrondissement): JsonResponse
    {
        $arrondissement->quartiers()->delete();
        $arrondissement->delete();

        return response()->json(null, 204);
    }

    public function createQuartier(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'arrondissement_id' => 'required|exists:arrondissements,id',
        ]);

        $quartier = Quartier::create($validated);

        return response()->json($quartier, 201);
    }

    public function updateQuartier(Request $request, Quartier $quartier): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'arrondissement_id' => 'required|exists:arrondissements,id',
        ]);

        $quartier->update($validated);

        return response()->json($quartier);
    }

    public function deleteQuartier(Quartier $quartier): JsonResponse
    {
        $quartier->delete();

        return response()->json(null, 204);
    }

    public function blockchainLogs(Request $request): JsonResponse
    {
        $query = BlockchainLog::query();

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        return response()->json($query->orderBy('id', 'desc')->paginate(20));
    }
}
