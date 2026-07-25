<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $activeSiteId = $request->session()->get('active_site_id');
        $roleFilter = $request->query('role');

        $users = User::query()
            ->with('roles', 'sites:id,code,name')
            ->when($activeSiteId, function ($q) use ($activeSiteId) {
                $q->whereHas('sites', fn ($sq) => $sq->where('sites.id', $activeSiteId))
                    ->orWhereDoesntHave('sites');
            })
            ->when($roleFilter, fn ($q) => $q->role($roleFilter))
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'is_active' => $user->is_active,
                'roles' => $user->getRoleNames()->toArray(),
                'sites' => $user->sites,
            ]);

        return Inertia::render('users/Index', [
            'users' => $users,
            'roleOptions' => UserRole::options(),
            'filters' => [
                'role' => $roleFilter,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('users/Create', [
            'roleOptions' => UserRole::options(),
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'];
        $siteIds = $data['site_ids'] ?? [];
        unset($data['role'], $data['site_ids']);

        $user = User::create($data);
        $user->assignRole($role);
        $user->sites()->sync($siteIds);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): Response
    {
        return Inertia::render('users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'is_active' => $user->is_active,
                'role' => $user->getRoleNames()->first(),
                'site_ids' => $user->sites()->pluck('sites.id'),
            ],
            'roleOptions' => UserRole::options(),
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $role = $data['role'];
        $siteIds = $data['site_ids'] ?? [];
        unset($data['role'], $data['site_ids']);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles([$role]);
        $user->sites()->sync($siteIds);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
