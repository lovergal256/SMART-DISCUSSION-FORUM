<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $notifications = Notification::where('UserID', $user->UserID)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.notifications.index', compact('notifications'));
    }

    public function markAsRead(Request $request)
    {
        $notificationIds = $request->input('notification_ids', []);
        Notification::whereIn('NotificationID', $notificationIds)
            ->update(['Status' => 'read']);

        return redirect()->back()->with('success', 'Notifications marked as read.');
    }
}
