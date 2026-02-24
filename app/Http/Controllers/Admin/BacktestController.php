<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Scoring\Jobs\BacktestSegmentJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BacktestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $results = DB::table('backtest_results')
            ->orderByDesc('computed_at')
            ->limit(50)
            ->get();
        return response()->json([
            'data' => $results->map(fn ($r) => [
                'id' => $r->id,
                'segment_key' => $r->segment_key,
                'from_date' => $r->from_date,
                'to_date' => $r->to_date,
                'accuracy_metric' => $r->accuracy_metric ? (float) $r->accuracy_metric : null,
                'computed_at' => $r->computed_at,
            ]),
        ]);
    }

    public function run(Request $request): JsonResponse
    {
        $request->validate([
            'segment_key' => 'required|string|max:120',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);
        BacktestSegmentJob::dispatch(
            $request->segment_key,
            $request->from_date,
            $request->to_date
        );
        return response()->json(['message' => 'Backtest job dispatched']);
    }
}
