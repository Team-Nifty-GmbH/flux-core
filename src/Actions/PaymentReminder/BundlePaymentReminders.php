<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Jobs\Accounting\SendPaymentReminderJob;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rulesets\PaymentReminder\BundlePaymentRemindersRuleset;
use Illuminate\Support\Facades\Bus;

class BundlePaymentReminders extends FluxAction
{
    public static function models(): array
    {
        return [PaymentReminder::class];
    }

    protected function getRulesets(): string|array
    {
        return BundlePaymentRemindersRuleset::class;
    }

    public function performAction(): array
    {
        $ordersInput = collect($this->getData('orders'));

        $orders = resolve_static(Order::class, 'query')
            ->whereIntegerInRaw('id', $ordersInput->pluck('id')->all())
            ->wherePaymentReminderEligible()
            ->get();

        $recipientById = $ordersInput->pluck('recipient', 'id');

        // One mail per invoice. All sends run as a single monitored batch (even a
        // batch of one) so the user gets the same progress toast as bulk mailing.
        $jobs = $orders
            ->map(fn (Order $order): SendPaymentReminderJob => app(SendPaymentReminderJob::class, [
                'orderId' => $order->getKey(),
                'recipientOverride' => data_get($recipientById, $order->getKey()),
            ]))
            ->all();

        if ($jobs) {
            Bus::monitoredBatch($jobs)
                ->name(__('Payment Reminders'))
                ->allowFailures()
                ->dispatch();
        }

        return ['queued' => count($jobs)];
    }
}
