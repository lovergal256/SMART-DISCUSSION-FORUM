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

   public function markAsRead(Request $request, $id)
{
    \Log::info('markAsRead called', ['id' => $id]);

    $updated = Notification::where('NotificationID', $id)->update(['Status' => 'read']);

    \Log::info('markAsRead result', ['rows_updated' => $updated]);

    return redirect()->back()->with('success', 'Notification marked as read.');
} 
}
