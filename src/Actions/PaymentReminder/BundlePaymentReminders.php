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
            ->with(['orderType:id,order_type_enum'])
            ->wherePaymentReminderEligible()
            ->get()
            ->filter(fn (Order $order) => ! $order->orderType->order_type_enum->isPurchase()
                && bccomp($order->orderType->order_type_enum->multiplier(), '1') === 0
            );

        $recipients = $this->getData('recipients') ?? [];

        // One mail per invoice. All sends run as a single monitored batch (even a
        // batch of one) so the user gets the same progress toast as bulk mailing.
        $jobs = $orders
            ->map(function (Order $order) use ($recipients): SendPaymentReminderJob {
                $key = $order->contact_id . '-' . ((int) $order->payment_reminder_current_level + 1);

                return new SendPaymentReminderJob($order->getKey(), data_get($recipients, $key));
            })
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
