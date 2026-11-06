<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ViewFacade::composer('layouts.dashboard', function (View $view): void {
            $user = auth()->user();
            $notificationCenterAvailable = Schema::hasTable('notifications');

            if (! $user instanceof User) {
                $view->with([
                    'unreadNotificationCount' => 0,
                    'recentNotifications' => collect(),
                    'notificationCenterAvailable' => $notificationCenterAvailable,
                ]);

                return;
            }

            if (! $notificationCenterAvailable) {
                $view->with([
                    'unreadNotificationCount' => 0,
                    'recentNotifications' => collect(),
                    'notificationCenterAvailable' => false,
                ]);

                return;
            }

            $view->with([
                'unreadNotificationCount' => $user->unreadNotifications()->count(),
                'recentNotifications' => $user->notifications()->latest()->limit(5)->get(),
                'notificationCenterAvailable' => true,
            ]);
        });
    }
}
