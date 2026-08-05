<?php

namespace FluxErp\Jobs\Accounting;

use Cron\CronExpression;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Models\Transaction;
use FluxErp\Support\Matching\LoanInstallmentMatcher;
use FluxErp\Support\Matching\OrderMatcher;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class MatchTransactionsWithOrderJob implements Repeatable, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public ?array $transactionIds = null
    ) {}

    public function __invoke(): void
    {
        $this->handle();
    }

    public static function defaultCron(): ?CronExpression
    {
        return new CronExpression('0 23 * * *');
    }

    public static function description(): ?string
    {
        return 'Try to match all open transactions with orders';
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return Str::headline(class_basename(static::class));
    }

    public static function parameters(): array
    {
        return [];
    }

    public static function withoutOverlapping(): bool
    {
        return true;
    }

    public static function repeatableType(): RepeatableTypeEnum
    {
        return RepeatableTypeEnum::Invokable;
    }

    public function handle(): void
    {
        $loanInstallmentMatcher = app(LoanInstallmentMatcher::class);
        $orderMatcher = app(OrderMatcher::class);
        $invoiceNumberPatterns = $orderMatcher->invoiceNumberPatterns();

        resolve_static(Transaction::class, 'query')
            ->when($this->transactionIds, fn (Builder $query) => $query->whereKey($this->transactionIds))
            ->whereNot('balance', 0)
            ->where('is_ignored', false)
            ->whereDoesntHave('orders', fn (Builder $query) => $query->where('is_accepted', false))
            ->whereDoesntHave('loanInstallments', fn (Builder $query) => $query->where('is_accepted', false))
            ->latest()
            ->cursor()
            ->each(function (Transaction $transaction) use (
                $loanInstallmentMatcher,
                $orderMatcher,
                $invoiceNumberPatterns
            ): void {
                if ($loanInstallmentMatcher->match($transaction)) {
                    return;
                }

                $orderMatcher->match($transaction, $invoiceNumberPatterns);
            });
    }
}
