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
        $orders = resolve_static(Order::class, 'query')
            ->whereIntegerInRaw('id', $this->getData('order_ids'))
            ->wherePaymentReminderEligible()
            ->get();

        $recipients = $this->getData('recipients') ?? [];

        // One mail per invoice. All sends run as a single monitored batch (even a
        // batch of one) so the user gets the same progress toast as bulk mailing.
        // Recipient overrides are keyed by order id to match the order_ids input.
        $jobs = $orders
            ->map(fn (Order $order): SendPaymentReminderJob => app(SendPaymentReminderJob::class, [
                'orderId' => $order->getKey(),
                'recipientOverride' => data_get($recipients, $order->getKey()),
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
