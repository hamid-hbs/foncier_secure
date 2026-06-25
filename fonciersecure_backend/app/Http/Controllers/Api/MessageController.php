<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        $user = $request->user();

        $sentIds = Message::where('sender_id', $user->id)
            ->where('messageable_type', 'App\Models\User')
            ->select('messageable_id')
            ->distinct()
            ->pluck('messageable_id');

        $receivedIds = Message::where('messageable_type', 'App\Models\User')
            ->where('messageable_id', $user->id)
            ->select('sender_id')
            ->distinct()
            ->pluck('sender_id');

        $contactIds = $sentIds->merge($receivedIds)->unique();

        $conversations = User::whereIn('id', $contactIds)->get()->map(function ($contact) use ($user) {
            $last = Message::where(function ($q) use ($user, $contact) {
                $q->where('sender_id', $user->id)->where('messageable_id', $contact->id)
                  ->orWhere('sender_id', $contact->id)->where('messageable_id', $user->id);
            })->where('messageable_type', 'App\Models\User')
                ->orderBy('created_at', 'desc')
                ->first();

            $nonLu = Message::where('messageable_type', 'App\Models\User')
                ->where('messageable_id', $user->id)
                ->where('sender_id', $contact->id)
                ->where('lu', false)
                ->count();

            return [
                'contact' => $contact,
                'dernier_message' => $last?->contenu,
                'date_dernier_message' => $last?->created_at,
                'non_lus' => $nonLu,
            ];
        })->sortByDesc('date_dernier_message')->values();

        return response()->json($conversations);
    }

    public function conversation(User $contact, Request $request): JsonResponse
    {
        $user = $request->user();

        $messages = Message::where(function ($q) use ($user, $contact) {
            $q->where('sender_id', $user->id)->where('messageable_id', $contact->id)
              ->orWhere('sender_id', $contact->id)->where('messageable_id', $user->id);
        })->where('messageable_type', 'App\Models\User')
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        Message::where('messageable_type', 'App\Models\User')
            ->where('messageable_id', $user->id)
            ->where('sender_id', $contact->id)
            ->where('lu', false)
            ->update(['lu' => true]);

        return response()->json($messages);
    }

    public function envoyer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'contenu' => 'required|string|max:5000',
        ]);

        if ($validated['receiver_id'] == $request->user()->id) {
            return response()->json(['message' => 'Vous ne pouvez pas vous envoyer un message à vous-même.'], 400);
        }

        $message = Message::create([
            'messageable_type' => 'App\Models\User',
            'messageable_id' => $validated['receiver_id'],
            'sender_id' => $request->user()->id,
            'contenu' => $validated['contenu'],
        ]);

        return response()->json($message->load('sender'), 201);
    }
}
