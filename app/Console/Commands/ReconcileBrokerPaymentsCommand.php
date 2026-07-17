<?php

namespace App\Console\Commands;

use App\Jobs\ReconcileBrokerPayment;
use App\Models\BrokerPaymentIntent;
use Illuminate\Console\Command;

class ReconcileBrokerPaymentsCommand extends Command
{
    protected $signature = 'payments:reconcile {--limit=500}';

    protected $description = 'Queue reconciliation of stale PayU payment intents';

    public function handle(): int
    {
        if (! config('services.payment_bridge.enabled')
            || ! config('services.payment_bridge.reconciliation_enabled')) {
            $this->components->info('Payment reconciliation is disabled.');

            return self::SUCCESS;
        }

        $cutoff = now()->subMinutes((int) config('services.payment_bridge.reconcile_after_minutes', 15));
        $count = 0;

        BrokerPaymentIntent::query()
            ->whereIn('status', ['pending', 'waiting_for_confirmation'])
            ->whereNotNull('payu_order_id')
            ->where(fn ($query) => $query
                ->whereNull('last_event_at')
                ->orWhere('last_event_at', '<=', $cutoff))
            ->where('updated_at', '<=', $cutoff)
            ->limit(max(1, min(5000, (int) $this->option('limit'))))
            ->pluck('id')
            ->each(function (int $id) use (&$count): void {
                ReconcileBrokerPayment::dispatch($id);
                $count++;
            });

        $this->components->info("Queued {$count} payment(s) for reconciliation.");

        return self::SUCCESS;
    }
}
