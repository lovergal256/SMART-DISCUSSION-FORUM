<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationApiController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::where('UserID', $user->UserID)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($n) {
                return [
                    'NotificationID' => $n->NotificationID,
                    'Message' => $n->Message,
                    'Type' => $n->Type,
                    'Status' => $n->Status,
                    'CreatedAt' => optional($n->created_at)?->toIso8601String(),
                ];
            });

        return response()->json($notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        Notification::where('NotificationID', $id)->update(['Status' => 'read']);

        return response()->json(['message' => 'Notification marked as read.']);
    }
}