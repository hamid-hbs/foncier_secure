<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = SupportTicket::with('user');

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sujet' => 'required|string|max:200',
            'message' => 'required|string|max:5000',
            'priorite' => 'nullable|in:basse, normale, haute, urgente',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'sujet' => $validated['sujet'],
            'message' => $validated['message'],
            'priorite' => $validated['priorite'] ?? 'normale',
        ]);

        return response()->json($ticket->load('user'), 201);
    }

    public function show(SupportTicket $supportTicket): JsonResponse
    {
        return response()->json($supportTicket->load(['user', 'assigne']));
    }

    public function repondre(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        $validated = $request->validate(['reponse' => 'required|string|max:10000']);

        $supportTicket->update([
            'reponse' => $validated['reponse'],
            'statut' => 'en_cours',
            'assigned_to' => $request->user()->id,
        ]);

        return response()->json($supportTicket);
    }

    public function updateStatut(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        $validated = $request->validate(['statut' => 'required|in:ouvert,en_cours,resolu,ferme']);

        $data = ['statut' => $validated['statut']];

        if ($validated['statut'] === 'ferme') {
            $data['closed_at'] = now();
        }

        $supportTicket->update($data);
        return response()->json($supportTicket);
    }
}
