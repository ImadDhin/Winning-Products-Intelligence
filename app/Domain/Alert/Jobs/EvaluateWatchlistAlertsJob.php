<?php

namespace App\Domain\Alert\Jobs;

use App\Domain\Alert\Models\Alert;
use App\Domain\Watchlist\Models\Watchlist;
use App\Domain\Product\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class EvaluateWatchlistAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(
        public ?int $userId = null,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $watchlists = Watchlist::query()
            ->when($this->userId !== null, fn ($q) => $q->where('user_id', $this->userId))
            ->with(['user', 'product'])
            ->get();
        foreach ($watchlists as $w) {
            $product = $w->product;
            if (! $product) {
                continue;
            }
            $threshold = $w->threshold_score !== null ? (float) $w->threshold_score : 70.0;
            if ((float) $product->current_score < $threshold) {
                continue;
            }
            $alerts = Alert::where('user_id', $w->user_id)
                ->where(fn ($q) => $q->where('watchlist_id', $w->id)->orWhere('product_id', $product->id))
                ->get();
            foreach ($alerts as $alert) {
                if ($alert->last_triggered_at && $alert->last_triggered_at->gt(now()->subHour())) {
                    continue;
                }
                $this->triggerAlert($alert, $product, $threshold);
                $alert->update(['last_triggered_at' => now()]);
            }
        }
    }

    private function triggerAlert(Alert $alert, Product $product, float $threshold): void
    {
        if ($alert->type === 'email') {
            $email = $alert->config['email'] ?? $alert->user->email;
            // Stub: send mail via Laravel Mail
            // Mail::to($email)->send(new WatchlistAlertMail($product, $threshold));
        }
        if ($alert->type === 'webhook') {
            $url = $alert->config['url'] ?? null;
            if ($url) {
                Http::timeout(5)->post($url, [
                    'product_id' => $product->id,
                    'score' => $product->current_score,
                    'threshold' => $threshold,
                ]);
            }
        }
    }
}
