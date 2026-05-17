<?php

namespace App\Http\Controllers;

use Illuminate\Notifications\DatabaseNotification;

class AdminNotificationController extends Controller
{
    public function index()
    {
        $notifications = DatabaseNotification::query()
            ->with('notifiable:id,name,email,role')
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $stats = [
            'total' => DatabaseNotification::query()->count(),
            'unread' => DatabaseNotification::query()->whereNull('read_at')->count(),
            'last_24h' => DatabaseNotification::query()
                ->where('created_at', '>=', now()->subDay())
                ->count(),
        ];

        return view('admin.notifications', [
            'notifications' => $notifications,
            'stats' => $stats,
        ]);
    }
}

