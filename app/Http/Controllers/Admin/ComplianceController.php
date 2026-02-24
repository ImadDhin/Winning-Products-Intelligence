<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Connector\Models\Source;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ComplianceController extends Controller
{
    public function index(): JsonResponse
    {
        $sources = Source::orderBy('name')->get();
        return response()->json([
            'data' => $sources->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
                'is_enabled' => $s->is_enabled,
                'compliance_notes' => $s->compliance_notes,
                'rate_limit_per_minute' => $s->rate_limit_per_minute,
                'last_run_at' => $s->last_run_at?->toIso8601String(),
                'consecutive_failures' => $s->consecutive_failures,
            ]),
            'notice' => 'Third-party scraping carries legal risk. Prefer official APIs. Respect robots.txt and rate limits. Connector toggles allow disabling sources.',
        ]);
    }
}
