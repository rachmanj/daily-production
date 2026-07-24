<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $sites = Site::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name', 'location']);

        $activeSiteId = $request->session()->get('active_site_id', $sites->first()?->id);
        $activeSite = $sites->firstWhere('id', $activeSiteId) ?? $sites->first();

        if ($activeSite && $request->session()->get('active_site_id') !== $activeSite->id) {
            $request->session()->put('active_site_id', $activeSite->id);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
                ] : null,
                'permissions' => $user?->getAllPermissions()->pluck('name')->toArray() ?? [],
            ],
            'sites' => $sites,
            'activeSite' => $activeSite,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
