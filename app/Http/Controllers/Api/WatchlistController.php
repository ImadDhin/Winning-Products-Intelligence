<?php

namespace App\Http\Controllers\Api;

use App\Domain\Watchlist\Models\Watchlist;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Watchlist::where('user_id', $request->user()->id)
            ->with('product:id,title_normalized,current_score,score_confidence')
            ->orderByDesc('created_at')
            ->get();
        return response()->json([
            'data' => $items->map(fn ($w) => [
                'id' => $w->id,
                'product_id' => $w->product_id,
                'threshold_score' => $w->threshold_score ? (float) $w->threshold_score : null,
                'product' => $w->product ? [
                    'id' => $w->product->id,
                    'title' => $w->product->title_normalized,
                    'current_score' => (float) $w->product->current_score,
                ] : null,
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'threshold_score' => 'nullable|numeric|min:0|max:100',
        ]);
        $w = Watchlist::firstOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $request->product_id],
            ['threshold_score' => $request->threshold_score]
        );
        if (! $w->wasRecentlyCreated) {
            $w->update(['threshold_score' => $request->threshold_score]);
        }
        return response()->json(['data' => ['id' => $w->id, 'product_id' => $w->product_id, 'threshold_score' => $w->threshold_score]], 201);
    }

    public function destroy(Request $request, int $product_id): JsonResponse
    {
        Watchlist::where('user_id', $request->user()->id)->where('product_id', $product_id)->delete();
        return response()->json(['message' => 'Removed from watchlist']);
    }
}
