<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureNotificationTableExists();

        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return view('notifications.index', [
            'notifications' => $user->notifications()->latest()->paginate(15),
            'unreadNotificationCount' => $user->unreadNotifications()->count(),
            'isAdmin' => $user->role === 'admin',
        ]);
    }

    public function markAsRead(Request $request, string $notificationId): RedirectResponse
    {
        $this->ensureNotificationTableExists();

        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $notification = $user->notifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        if ($notification instanceof DatabaseNotification && $notification->unread()) {
            $notification->markAsRead();
        }

        return redirect()->route('notifications.index')
            ->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->ensureNotificationTableExists();

        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $user->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->route('notifications.index')
            ->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function ensureNotificationTableExists(): void
    {
        abort_unless(
            Schema::hasTable('notifications'),
            503,
            'Pusat notifikasi belum aktif karena migration database belum diterapkan.',
        );
    }
}
