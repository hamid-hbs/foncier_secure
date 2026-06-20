<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoleRequest;
use App\Models\User;
use App\Helpers\EncryptionHelper;
use App\Notifications\OtpNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'required|email|unique:users',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'citoyen';

        $user = User::create($validated);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Compte désactivé'], 403);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
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
        return response()->json($request->user()->load('professionnel'));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:100',
            'prenom' => 'sometimes|string|max:100',
            'telephone' => 'sometimes|string|max:20',
        ]);

        $user->update($validated);

        return response()->json($user);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $user = User::where('email', $validated['email'])->first();
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(15),
        ]);

        $user->notify(new OtpNotification($otp));

        return response()->json(['message' => 'OTP envoyé']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users',
            'otp' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $validated['email'])
            ->where('otp', $validated['otp'])
            ->where('otp_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json(['message' => 'OTP invalide ou expiré'], 400);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return response()->json(['message' => 'Mot de passe réinitialisé']);
    }

    public function requestRole(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role_demande' => 'required|in:geometre,notaire',
            'document_justificatif' => 'required|file|mimes:pdf,jpg,png|max:5120',
        ]);

        $file = $request->file('document_justificatif');
        $content = file_get_contents($file->getRealPath());
        $path = 'role-requests/' . uniqid() . '.' . $file->extension();
        EncryptionHelper::storeEncrypted($path, $content);

        $roleRequest = RoleRequest::create([
            'user_id' => $request->user()->id,
            'role_demande' => $validated['role_demande'],
            'document_justificatif' => $path,
        ]);

        return response()->json($roleRequest, 201);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->tokens()->delete();

        $user->professionnel?->delete();
        $user->verifications()->each(fn($v) => $v->delete());

        Storage::deleteDirectory('coffre/' . $user->id);

        $user->delete();

        return response()->json(['message' => 'Compte et toutes les données associées supprimés.']);
    }
}
