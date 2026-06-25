<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $rules = [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:citoyen,geometre,notaire',
            'type_piece_identite' => 'required|in:cnib,passeport',
            'piece_identite' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];

        if ($request->input('role') !== 'citoyen') {
            $rules['numero_enregistrement'] = 'required|string|max:50';
            $rules['adresse_bureau'] = 'required|string|max:255';
            $rules['specialisation'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $role = Role::where('nom', $validated['role'])->firstOrFail();

        $path = $request->file('piece_identite')->store('identites');

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'telephone' => $validated['telephone'] ?? null,
            'password_hash' => Hash::make($validated['password']),
            'role_id' => $role->id,
            'type_piece_identite' => $validated['type_piece_identite'],
            'piece_identite_path' => $path,
            'is_active' => false,
        ]);

        if ($validated['role'] !== 'citoyen') {
            $user->professionnel()->create([
                'type' => $validated['role'],
                'numero_enregistrement' => $validated['numero_enregistrement'],
                'date_enregistrement' => now()->toDateString(),
                'adresse_bureau' => $validated['adresse_bureau'],
                'specialisation' => $validated['specialisation'] ?? null,
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('role'),
            'token' => $token,
            'message' => 'Inscription réussie. Votre compte est en attente de validation par un administrateur.',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password_hash)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Votre compte est en attente de validation par un administrateur.',
                'code' => 'ACCOUNT_PENDING_APPROVAL',
            ], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user->load('role'),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté']);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json($request->user()->load(['role', 'professionnel']));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'telephone' => 'sometimes|string|max:20',
            'photo_profil' => 'sometimes|string|max:255',
        ]);

        $user->update($validated);
        return response()->json($user->load('role'));
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user()->load(['role', 'professionnel']);

        return response()->json([
            'user' => $user,
            'is_active' => $user->is_active,
            'status' => $user->is_active ? 'actif' : 'en_attente_validation',
            'message' => $user->is_active
                ? 'Bienvenue sur votre tableau de bord.'
                : 'Votre compte est en attente de validation par un administrateur.',
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $user->professionnel?->delete();
        Storage::deleteDirectory('documents/' . $user->id);
        $user->delete();

        return response()->json(['message' => 'Compte supprimé.']);
    }
}
