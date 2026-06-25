<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Notification::where('notifiable_id', $request->user()->id)
                ->where('notifiable_type', 'App\Models\User')
                ->orderBy('created_at', 'desc')
                ->paginate(20)
        );
    }

    public function markAsRead(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->notifiable_id !== $request->user()->id) {
            return response()->json(['message' => 'Accès refusé'], 403);
        }

        $notification->update(['read_at' => now()]);
        return response()->json($notification);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = Notification::where('notifiable_id', $request->user()->id)
            ->where('notifiable_type', 'App\Models\User')
            ->whereNull('read_at')
            ->count();

        return response()->json(['non_lues' => $count]);
    }
}
