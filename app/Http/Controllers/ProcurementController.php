<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProcurementController extends Controller
{
    public function index(Request $request): Response
    {
        $siteId = $request->integer('site_id') ?: $request->session()->get('active_site_id');
        $year = $request->integer('year', now()->year);
        $month = $request->integer('month', now()->month);

        return Inertia::render('procurement/Index', [
            'sites' => Site::query()->where('is_active', true)->orderBy('code')->get(['id', 'code', 'name']),
            'filters' => compact('siteId', 'year', 'month'),
        ]);
    }
}
