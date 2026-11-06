<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IndexResidentsRequest;
use App\Http\Requests\Admin\UpdateResidentRequest;
use App\Http\Requests\Admin\UpdateResidentStatusRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(IndexResidentsRequest $request): View
    {
        $validated = $request->validated();
        $statusAvailable = Schema::hasColumn('users', 'is_active');

        abort_if(
            ! $statusAvailable && isset($validated['status']),
            503,
            'Filter status akun tersedia setelah pembaruan database diterapkan.',
        );

        $residents = User::query()
            ->where('role', 'warga')
            ->withCount('laporans')
            ->when($validated['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when(
                $statusAvailable && isset($validated['status']),
                fn ($query) => $query->where('is_active', $validated['status'] === 'active'),
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'residents' => $residents,
            'statusAvailable' => $statusAvailable,
        ]);
    }

    public function show(User $user): View
    {
        abort_unless(request()->user()?->can('view', $user), 403);

        return view('admin.users.show', [
            'resident' => $user->loadCount('laporans'),
            'reports' => $user->laporans()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(10),
            'statusAvailable' => Schema::hasColumn('users', 'is_active'),
        ]);
    }

    public function update(UpdateResidentRequest $request, User $user): RedirectResponse
    {
        $user->update($request->safe()->only(['name', 'email']));

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Profil warga berhasil diperbarui.');
    }

    public function updateStatus(UpdateResidentStatusRequest $request, User $user): RedirectResponse
    {
        abort_unless(
            Schema::hasColumn('users', 'is_active'),
            503,
            'Pengelolaan status akun tersedia setelah pembaruan database diterapkan.',
        );

        $user->is_active = $request->validated('is_active');
        $user->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', $user->is_active ? 'Akun warga berhasil diaktifkan.' : 'Akun warga berhasil dinonaktifkan.');
    }
}
