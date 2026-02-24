<?php

namespace App\Http\Controllers\Api;

use App\Domain\Product\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $product = Product::with([
            'variants',
            'assets',
            'category',
            'score',
            'sourceSnapshots' => fn ($q) => $q->with('source')->orderByDesc('fetched_at')->limit(20),
        ])->findOrFail($id);

        $latestPerSource = $product->sourceSnapshots->groupBy('source_id')->map->first();
        $sourceLinks = $latestPerSource->map(fn ($s) => [
            'source_id' => $s->source_id,
            'external_id' => $s->external_id,
            'fetched_at' => $s->fetched_at?->toIso8601String(),
        ])->values()->all();

        $chart = $product->metricsTimeSeries()
            ->where('ts_bucket', '>=', now()->subDays(30))
            ->orderBy('ts_bucket')
            ->get()
            ->groupBy('metric_key')
            ->map(fn ($rows) => $rows->map(fn ($r) => ['ts' => $r->ts_bucket->toIso8601String(), 'value' => (float) $r->value])->values()->all())
            ->all();

        $score = $product->score;
        return response()->json([
            'id' => $product->id,
            'title' => $product->title_normalized,
            'brand' => $product->brand_normalized,
            'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name] : null,
            'current_score' => (float) $product->current_score,
            'score_confidence' => (float) $product->score_confidence,
            'confidence_band' => $this->confidenceBand($product->score_confidence),
            'score_breakdown' => $score?->components ?? [],
            'variants' => $product->variants->map(fn ($v) => [
                'id' => $v->id,
                'sku' => $v->sku,
                'options' => $v->options,
                'price_min' => (float) $v->price_min,
                'price_max' => (float) $v->price_max,
                'currency' => $v->currency,
            ])->all(),
            'assets' => $product->assets->map(fn ($a) => ['type' => $a->type, 'url' => $a->url])->all(),
            'source_links' => $sourceLinks,
            'historical_chart' => $chart,
        ]);
    }

    private function confidenceBand(float $c): string
    {
        return $c >= 0.7 ? 'High' : ($c >= 0.4 ? 'Medium' : 'Low');
    }
}
