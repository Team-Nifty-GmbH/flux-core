<?php

namespace FluxErp\Jobs\Accounting;

use Cron\CronExpression;
use FluxErp\Actions\PaymentReminder\BundlePaymentReminders;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Models\Order;
use FluxErp\Settings\AccountingSettings;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class AutoSendPaymentRemindersJob implements Repeatable, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable;

    public function __construct(
        public ?array $orderIds = null
    ) {}

    public function __invoke(): void
    {
        $this->handle();
    }

    public static function defaultCron(): ?CronExpression
    {
        return new CronExpression('0 0 * * *');
    }

    public static function description(): ?string
    {
        return 'Automatically send payment reminders for overdue invoices';
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
        if (! app(AccountingSettings::class)->auto_send_reminders) {
            return;
        }

        $orderIds = resolve_static(Order::class, 'query')
            ->when($this->orderIds, fn (Builder $query) => $query->whereKey($this->orderIds))
            ->with(['orderType:id,order_type_enum'])
            ->whereNotNull('invoice_number')
            ->where('is_locked', true)
            ->where('balance', '!=', 0)
            ->whereDate('payment_reminder_next_date', '<=', now()->toDateString())
            ->whereHasMailablePaymentReminderAddress()
            ->get(['id', 'order_type_id'])
            ->filter(fn (Order $order) => ! $order->orderType->order_type_enum->isPurchase()
                && $order->orderType->order_type_enum->multiplier() === 1
            )
            ->pluck('id')
            ->all();

        if (! $orderIds) {
            return;
        }

        BundlePaymentReminders::make(['order_ids' => $orderIds])
            ->validate()
            ->execute();
    }
}
