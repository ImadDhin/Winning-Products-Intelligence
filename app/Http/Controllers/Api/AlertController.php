<?php

namespace App\Http\Controllers\Api;

use App\Domain\Alert\Models\Alert;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $alerts = Alert::where('user_id', $request->user()->id)->with(['watchlist', 'product'])->get();
        return response()->json([
            'data' => $alerts->map(fn ($a) => [
                'id' => $a->id,
                'type' => $a->type,
                'config' => $a->config,
                'watchlist_id' => $a->watchlist_id,
                'product_id' => $a->product_id,
                'last_triggered_at' => $a->last_triggered_at?->toIso8601String(),
            ]),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'watchlist_id' => 'nullable|integer|exists:watchlists,id',
            'product_id' => 'nullable|integer|exists:products,id',
            'type' => 'required|in:email,webhook',
            'config' => 'nullable|array',
        ]);
        if (! $request->watchlist_id && ! $request->product_id) {
            return response()->json(['message' => 'Either watchlist_id or product_id is required'], 422);
        }
        $alert = Alert::create([
            'user_id' => $request->user()->id,
            'watchlist_id' => $request->watchlist_id,
            'product_id' => $request->product_id,
            'type' => $request->type,
            'config' => $request->config ?? [],
        ]);
        return response()->json(['data' => ['id' => $alert->id, 'type' => $alert->type]], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $alert = Alert::where('user_id', $request->user()->id)->findOrFail($id);
        $request->validate(['type' => 'sometimes|in:email,webhook', 'config' => 'sometimes|array']);
        $alert->update($request->only(['type', 'config']));
        return response()->json(['data' => ['id' => $alert->id]]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        Alert::where('user_id', $request->user()->id)->where('id', $id)->delete();
        return response()->json(['message' => 'Alert deleted']);
    }
}
