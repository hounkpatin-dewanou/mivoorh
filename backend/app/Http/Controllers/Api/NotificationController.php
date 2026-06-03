<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->appNotifications()->orderByDesc('created_at')->get()
        );
    }

    public function markRead(Request $request, AppNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->update(['is_read' => true]);

        return response()->json($notification);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->appNotifications()->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['message' => 'Toutes les notifications sont lues.']);
    }
}
