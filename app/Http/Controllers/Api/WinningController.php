<?php

namespace App\Http\Controllers\Api;

use App\Domain\Leaderboard\Services\LeaderboardService;
use App\Domain\Product\DTOs\ProductCardDto;
use App\Domain\Product\Models\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WinningController extends Controller
{
    public function __construct(
        private LeaderboardService $leaderboard,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $categoryId = $request->integer('category_id', 0) ?: null;
        $window = $request->get('window', '24h');
        if (! in_array($window, ['24h', '7d', '30d'], true)) {
            $window = '24h';
        }
        $segmentKey = $categoryId ? "category:{$categoryId}:{$window}" : "default:{$window}";
        $page = max(1, $request->integer('page', 1));
        $perPage = min(50, max(1, $request->integer('per_page', 20)));
        $offset = ($page - 1) * $perPage;

        $productIds = $this->leaderboard->getProductIdsForSegment($segmentKey, $offset, $perPage);
        if ($productIds === []) {
            return response()->json(['data' => [], 'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => 0]]);
        }

        $cards = $this->hydrateCards($productIds);
        return response()->json([
            'data' => array_map(fn (ProductCardDto $dto) => $this->dtoToArray($dto), $cards),
            'meta' => ['current_page' => $page, 'per_page' => $perPage, 'total' => count($productIds)],
        ]);
    }

    /** @return list<ProductCardDto> */
    private function hydrateCards(array $productIds): array
    {
        $order = array_flip($productIds);
        $out = [];
        $miss = [];
        foreach ($productIds as $id) {
            $cached = Cache::get('product:card:' . $id);
            if ($cached !== null && is_array($cached)) {
                $out[$order[$id]] = new ProductCardDto(
                    id: $cached['id'],
                    title: $cached['title'],
                    score: $cached['score'],
                    confidence: $cached['confidence'],
                    thumbnail: $cached['thumbnail'],
                    priceMin: $cached['price_min'],
                    priceMax: $cached['price_max'],
                    currency: $cached['currency'],
                );
            } else {
                $miss[] = $id;
            }
        }
        if ($miss !== []) {
            $products = Product::with(['assets', 'variants'])->whereIn('id', $miss)->get()->keyBy('id');
            $ttl = config('winning.product_card_ttl', 300);
            foreach ($miss as $id) {
                $p = $products->get($id);
                if (! $p) {
                    continue;
                }
                $vars = $p->variants;
                $dto = new ProductCardDto(
                    id: $p->id,
                    title: $p->title_normalized,
                    score: (float) $p->current_score,
                    confidence: (float) $p->score_confidence,
                    thumbnail: $p->assets->first()?->url,
                    priceMin: $vars->isEmpty() ? null : (float) $vars->min('price_min'),
                    priceMax: $vars->isEmpty() ? null : (float) $vars->max('price_max'),
                    currency: $vars->isEmpty() ? 'USD' : ($vars->first()?->currency ?? 'USD'),
                );
                Cache::put('product:card:' . $id, [
                    'id' => $dto->id,
                    'title' => $dto->title,
                    'score' => $dto->score,
                    'confidence' => $dto->confidence,
                    'thumbnail' => $dto->thumbnail,
                    'price_min' => $dto->priceMin,
                    'price_max' => $dto->priceMax,
                    'currency' => $dto->currency,
                ], $ttl);
                $out[$order[$id]] = $dto;
            }
        }
        ksort($out);
        return array_values($out);
    }

    private function dtoToArray(ProductCardDto $dto): array
    {
        return [
            'id' => $dto->id,
            'title' => $dto->title,
            'score' => $dto->score,
            'confidence' => $dto->confidence,
            'confidence_band' => $dto->confidenceBand(),
            'thumbnail' => $dto->thumbnail,
            'price_min' => $dto->priceMin,
            'price_max' => $dto->priceMax,
            'currency' => $dto->currency,
        ];
    }
}
