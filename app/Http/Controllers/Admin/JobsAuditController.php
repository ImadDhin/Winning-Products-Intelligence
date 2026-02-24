<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Connector\Models\JobAudit;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobsAuditController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = JobAudit::with('source')->orderByDesc('started_at');
        if ($request->filled('source_id')) {
            $query->where('source_id', $request->source_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $items = $query->limit(100)->get();
        return response()->json([
            'data' => $items->map(fn ($j) => [
                'id' => $j->id,
                'source_id' => $j->source_id,
                'source_name' => $j->source?->name,
                'type' => $j->type,
                'started_at' => $j->started_at?->toIso8601String(),
                'finished_at' => $j->finished_at?->toIso8601String(),
                'status' => $j->status,
                'items_processed' => $j->items_processed,
                'error_message' => $j->error_message,
                'rate_limit_hits' => $j->rate_limit_hits,
            ]),
        ]);
    }
}
