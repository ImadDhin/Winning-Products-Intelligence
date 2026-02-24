<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Connector\Models\Source;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConnectorController extends Controller
{
    public function index(): JsonResponse
    {
        $sources = Source::orderBy('name')->get();
        return response()->json([
            'data' => $sources->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
                'connector_class' => $s->connector_class,
                'is_enabled' => $s->is_enabled,
                'last_run_at' => $s->last_run_at?->toIso8601String(),
                'rate_limit_per_minute' => $s->rate_limit_per_minute,
                'consecutive_failures' => $s->consecutive_failures,
                'schedule_cron' => $s->schedule_cron,
            ]),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $source = Source::findOrFail($id);
        return response()->json([
            'id' => $source->id,
            'name' => $source->name,
            'slug' => $source->slug,
            'connector_class' => $source->connector_class,
            'config' => $source->config,
            'is_enabled' => $source->is_enabled,
            'last_run_at' => $source->last_run_at?->toIso8601String(),
            'rate_limit_per_minute' => $source->rate_limit_per_minute,
            'consecutive_failures' => $source->consecutive_failures,
            'compliance_notes' => $source->compliance_notes,
            'schedule_cron' => $source->schedule_cron,
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $source = Source::findOrFail($id);
        $request->validate([
            'is_enabled' => 'sometimes|boolean',
            'rate_limit_per_minute' => 'sometimes|integer|min:1|max:300',
            'schedule_cron' => 'sometimes|string|max:100',
            'compliance_notes' => 'sometimes|nullable|string',
            'config' => 'sometimes|array',
        ]);
        $source->update($request->only([
            'is_enabled', 'rate_limit_per_minute', 'schedule_cron', 'compliance_notes', 'config',
        ]));
        return response()->json(['data' => ['id' => $source->id]]);
    }
}
