<?php

namespace FluxErp\Console\Commands;

use FluxErp\Models\Transaction;
use Illuminate\Console\Command;

class RecalculateTransactionBalances extends Command
{
    protected $description = 'Recalculate stored transaction balances that drifted away from their assignments';

    protected $signature = 'flux:recalculate-transaction-balances {--apply : Write the corrected balances instead of listing them}';

    public function handle(): int
    {
        $drifted = [];

        resolve_static(Transaction::class, 'query')
            ->where('is_ignored', false)
            ->whereNull('contact_bank_connection_id')
            ->chunkById(500, function ($transactions) use (&$drifted): void {
                foreach ($transactions as $transaction) {
                    $stored = (string) $transaction->balance;
                    $expected = (string) $transaction->calculateBalance()->balance;

                    if (bccomp($stored, $expected, 2) === 0) {
                        continue;
                    }

                    $drifted[] = [
                        $transaction->getKey(),
                        $transaction->value_date?->toDateString(),
                        $transaction->amount,
                        $stored,
                        $expected,
                    ];

                    if ($this->option('apply')) {
                        $transaction->save();
                    }
                }
            });

        if (! $drifted) {
            $this->info('All transaction balances match their assignments.');

            return self::SUCCESS;
        }

        $this->table(['ID', 'Value date', 'Amount', 'Stored', 'Expected'], $drifted);

        if (! $this->option('apply')) {
            $this->warn(
                count($drifted) . ' balance(s) drifted. Run again with --apply to write the corrected values.'
            );
        } else {
            $this->info(count($drifted) . ' balance(s) corrected.');
        }

        return self::SUCCESS;
    }
}
