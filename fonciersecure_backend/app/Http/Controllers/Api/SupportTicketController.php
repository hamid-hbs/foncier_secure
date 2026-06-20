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
        $query = SupportTicket::with(['user', 'assigne']);

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sujet' => 'required|string|max:200',
            'message' => 'required|string',
            'priorite' => 'sometimes|in:basse,normale,haute,urgente',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'sujet' => $validated['sujet'],
            'message' => $validated['message'],
            'priorite' => $validated['priorite'] ?? 'normale',
        ]);

        return response()->json($ticket->load('user'), 201);
    }

    public function show(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        if ($request->user()->role !== 'admin' && $supportTicket->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        return response()->json($supportTicket->load(['user', 'assigne']));
    }

    public function repondre(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'reponse' => 'required|string',
            'statut' => 'sometimes|in:en_cours,resolu',
        ]);

        $supportTicket->update([
            'reponse' => $validated['reponse'],
            'assigned_to' => $supportTicket->assigned_to ?? $request->user()->id,
            'statut' => $validated['statut'] ?? 'resolu',
            'closed_at' => ($validated['statut'] ?? 'resolu') === 'resolu' ? now() : $supportTicket->closed_at,
        ]);

        return response()->json($supportTicket->load(['user', 'assigne']));
    }

    public function updateStatut(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $validated = $request->validate([
            'statut' => 'required|in:ouvert,en_cours,resolu,ferme',
        ]);

        $supportTicket->update([
            'statut' => $validated['statut'],
            'closed_at' => in_array($validated['statut'], ['resolu', 'ferme']) ? now() : null,
        ]);

        return response()->json($supportTicket);
    }
}
