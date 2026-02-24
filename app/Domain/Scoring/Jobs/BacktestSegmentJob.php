<?php

namespace App\Domain\Scoring\Jobs;

use App\Domain\Product\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class BacktestSegmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public string $segmentKey,
        public string $fromDate,
        public string $toDate,
    ) {
        $this->onQueue('scoring');
    }

    public function handle(): void
    {
        $parts = explode(':', $this->segmentKey);
        $categoryId = isset($parts[1]) && is_numeric($parts[1]) ? (int) $parts[1] : null;
        $query = Product::query()
            ->whereNotNull('current_score')
            ->orderByDesc('current_score')
            ->limit(100);
        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }
        $predictedIds = $query->pluck('id')->all();
        $actualOutcomes = [];
        foreach ($predictedIds as $id) {
            $metrics = DB::table('product_metrics_time_series')
                ->where('product_id', $id)
                ->whereBetween('ts_bucket', [$this->fromDate, $this->toDate])
                ->get()
                ->groupBy('metric_key')
                ->map(fn ($rows) => $rows->avg('value'))
                ->all();
            $actualOutcomes[$id] = $metrics;
        }
        $accuracy = count($predictedIds) > 0 ? 0.5 : 0.0; // placeholder: compare to actual outcomes
        DB::table('backtest_results')->insert([
            'segment_key' => $this->segmentKey,
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'predicted_winning_ids' => json_encode($predictedIds),
            'actual_outcomes' => json_encode($actualOutcomes),
            'accuracy_metric' => $accuracy,
            'computed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
