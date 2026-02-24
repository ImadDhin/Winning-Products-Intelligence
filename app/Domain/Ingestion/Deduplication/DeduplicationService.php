<?php

namespace App\Domain\Ingestion\Deduplication;

use App\Domain\Ingestion\DTOs\NormalizedProductDto;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductAsset;
use App\Domain\Product\Models\ProductSourceSnapshot;
use App\Domain\Product\Models\ProductVariant;
use App\Domain\Product\Models\Category;
use Illuminate\Support\Str;

class DeduplicationService
{
    public function fingerprint(NormalizedProductDto $dto): string
    {
        $title = $this->normalizeTitle($dto->title);
        $brand = $this->normalizeTitle($dto->brand ?? '');
        $priceBand = $this->priceBand($dto->price, $dto->variants);
        $key = implode('|', [
            $title,
            $brand,
            $dto->category_external_id ?? '',
            $priceBand,
            $this->imageHashPlaceholder($dto->images),
        ]);
        return hash('sha256', $key);
    }

    public function mergeOrCreate(NormalizedProductDto $dto, string $fingerprint, int $sourceId, string $externalId, array $rawPayload, array $normalized, \DateTimeInterface $fetchedAt): Product
    {
        $product = Product::where('fingerprint_hash', $fingerprint)->first();
        if ($product) {
            $this->attachSnapshot($product->id, $sourceId, $externalId, $rawPayload, $normalized, $fetchedAt);
            return $product;
        }
        $categoryId = null;
        if ($dto->category_external_id) {
            $cat = Category::whereJsonContains('external_ids->' . $dto->category_external_id, $dto->category_external_id)
                ->orWhere('slug', Str::slug($dto->category_external_id))->first();
            $categoryId = $cat?->id;
        }
        $product = Product::create([
            'fingerprint_hash' => $fingerprint,
            'title_normalized' => $this->normalizeTitle($dto->title),
            'brand_normalized' => $this->normalizeTitle($dto->brand ?? ''),
            'category_id' => $categoryId,
            'first_seen_at' => $fetchedAt,
        ]);
        $this->attachSnapshot($product->id, $sourceId, $externalId, $rawCompressed, $normalized, $fetchedAt);
        $this->upsertVariants($product->id, $dto);
        $this->upsertAssets($product->id, $dto);
        return $product;
    }

    private function normalizeTitle(?string $s): string
    {
        if ($s === null || $s === '') {
            return '';
        }
        return Str::lower(Str::limit(preg_replace('/\s+/', ' ', trim($s)), 500));
    }

    private function priceBand(?float $price, array $variants): string
    {
        if ($variants !== []) {
            $prices = array_column($variants, 'price');
            $min = min($prices);
            $max = max($prices);
        } else {
            $min = $max = $price ?? 0;
        }
        $band = $min <= 0 ? 0 : (int) (log10($min + 1) * 10);
        return (string) $band;
    }

    private function imageHashPlaceholder(array $images): string
    {
        if ($images === []) {
            return '';
        }
        return hash('sha256', implode('', array_slice($images, 0, 3)));
    }

    private function attachSnapshot(int $productId, int $sourceId, string $externalId, array $rawPayload, array $normalized, \DateTimeInterface $fetchedAt): void
    {
        $compressed = !empty($rawPayload) ? gzcompress(json_encode($rawPayload), 6) : null;
        ProductSourceSnapshot::create([
            'product_id' => $productId,
            'source_id' => $sourceId,
            'external_id' => $externalId,
            'raw_payload_compressed' => $compressed,
            'normalized' => $normalized,
            'fetched_at' => $fetchedAt,
            'metrics_snapshot' => $normalized['metrics'] ?? null,
        ]);
    }

    private function upsertVariants(int $productId, NormalizedProductDto $dto): void
    {
        if ($dto->variants === []) {
            $price = $dto->price ?? 0.0;
            ProductVariant::create([
                'product_id' => $productId,
                'price_min' => $price,
                'price_max' => $price,
                'currency' => $dto->currency,
            ]);
            return;
        }
        foreach ($dto->variants as $v) {
            $p = (float) ($v['price'] ?? $v['price_min'] ?? 0);
            ProductVariant::updateOrCreate(
                [
                    'product_id' => $productId,
                    'sku' => $v['sku'] ?? null,
                ],
                [
                    'options' => $v['options'] ?? $v,
                    'price_min' => $p,
                    'price_max' => $p,
                    'currency' => $dto->currency,
                ]
            );
        }
    }

    private function upsertAssets(int $productId, NormalizedProductDto $dto): void
    {
        $sort = 0;
        foreach ($dto->images as $url) {
            ProductAsset::firstOrCreate(
                [
                    'product_id' => $productId,
                    'url' => is_string($url) ? $url : ($url['url'] ?? ''),
                ],
                ['type' => 'image', 'sort_order' => $sort++]
            );
        }
    }
}
