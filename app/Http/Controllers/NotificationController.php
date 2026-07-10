<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
{
    $notifications = Notification::where('UserID', Auth::id())
        ->orderByDesc('NotificationID')
        ->get();

    return match (Auth::user()->RoleID) {
        1 => view('student.notifications.index', compact('notifications')),
        2 => view('lecturer.notifications.index', compact('notifications')),
        3 => view('admin.notifications.index', compact('notifications')),
        default => abort(403),
    };
}

    public function markAsRead(Request $request)
    {
        $notificationIds = $request->input('notification_ids', []);
        Notification::whereIn('NotificationID', $notificationIds)
            ->update(['Status' => 'read']);

        return redirect()->back()->with('success', 'Notifications marked as read.');
    }
}
