<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('dashboard/Index', [
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'filters' => [
                'site_id' => $request->integer('site_id') ?: $request->session()->get('active_site_id'),
                'date' => $request->string('date', now()->toDateString()),
            ],
        ]);
    }

    public function fuel(Request $request): Response
    {
        return Inertia::render('dashboard/Fuel', [
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'filters' => [
                'site_id' => $request->integer('site_id') ?: $request->session()->get('active_site_id'),
                'date' => $request->string('date', now()->toDateString()),
            ],
        ]);
    }
}
