<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ScoringWeightsController extends Controller
{
    public function index(): JsonResponse
    {
        $weights = config('winning.weights', []);
        return response()->json(['data' => $weights]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'demand_growth' => 'sometimes|numeric|min:0|max:1',
            'competition' => 'sometimes|numeric|min:0|max:1',
            'margin_potential' => 'sometimes|numeric|min:0|max:1',
            'stability' => 'sometimes|numeric|min:0|max:1',
            'freshness' => 'sometimes|numeric|min:0|max:1',
            'seasonality' => 'sometimes|numeric|min:0|max:1',
        ]);
        $path = config_path('winning.php');
        $content = File::get($path);
        foreach ($request->only(array_keys(config('winning.weights'))) as $key => $value) {
            $content = preg_replace(
                "/'{$key}'\s*=>\s*[\d.]+/",
                "'{$key}' => " . (float) $value,
                $content
            );
        }
        File::put($path, $content);
        return response()->json(['data' => config('winning.weights')]);
    }
}
