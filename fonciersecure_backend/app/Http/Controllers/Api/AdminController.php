<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Arrondissement;
use App\Models\BlockchainLog;
use App\Models\Commune;
use App\Models\Professionnel;
use App\Models\Quartier;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function createUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:citoyen,geometre,notaire,admin',
            'is_active' => 'boolean',
        ]);

        $role = Role::where('nom', $validated['role'])->firstOrFail();

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'role_id' => $role->id,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($user->load('role'), 201);
    }
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'total_users' => User::count(),
            'total_citoyens' => User::whereHas('role', fn($q) => $q->where('nom', 'citoyen'))->count(),
            'total_geometres' => User::whereHas('role', fn($q) => $q->where('nom', 'geometre'))->count(),
            'total_notaires' => User::whereHas('role', fn($q) => $q->where('nom', 'notaire'))->count(),
            'pending_users' => User::where('is_active', false)->count(),
            'recent_users' => User::with('role')->latest()->take(10)->get(),
        ]);
    }

    public function users(): JsonResponse
    {
        return response()->json(
            User::with(['role', 'professionnel'])->orderBy('created_at', 'desc')->paginate(20)
        );
    }

    public function pendingUsers(): JsonResponse
    {
        return response()->json(
            User::with(['role', 'professionnel'])->where('is_active', false)->orderBy('created_at', 'desc')->paginate(20)
        );
    }

    public function approveUser(User $user): JsonResponse
    {
        if ($user->is_active) {
            return response()->json(['message' => 'Ce compte est déjà actif.'], 400);
        }

        $user->update(['is_active' => true]);

        return response()->json([
            'message' => 'Compte approuvé avec succès.',
            'user' => $user->fresh()->load('role'),
        ]);
    }

    public function toggleUserStatus(User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);
        return response()->json($user->load('role'));
    }

    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate(['role_code' => 'required|in:citoyen,geometre,notaire,admin']);
        $role = Role::where('nom', $validated['role_code'])->firstOrFail();
        $user->update(['role_id' => $role->id]);

        return response()->json($user->load('role'));
    }

    public function createProfessionnel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:geometre,notaire',
            'numero_enregistrement' => 'nullable|string|max:50',
            'adresse_bureau' => 'nullable|string|max:255',
            'specialisation' => 'nullable|string|max:255',
        ]);

        return response()->json(Professionnel::create($validated)->load('user'), 201);
    }

    public function createCommune(Request $request): JsonResponse
    {
        return response()->json(Commune::create($request->validate(['nom' => 'required|string|max:100'])), 201);
    }

    public function updateCommune(Request $request, Commune $commune): JsonResponse
    {
        $commune->update($request->validate(['nom' => 'required|string|max:100']));
        return response()->json($commune);
    }

    public function deleteCommune(Commune $commune): JsonResponse
    {
        $commune->load('arrondissements.quartiers');
        foreach ($commune->arrondissements as $arr) {
            $arr->quartiers()->delete();
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
        return response()->json(Arrondissement::create($validated), 201);
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
        return response()->json(Quartier::create($validated), 201);
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
