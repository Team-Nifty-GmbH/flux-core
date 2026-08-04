<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Jobs\Accounting\SendPaymentReminderJob;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentReminderText;
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

        // A reminder can only be mailed when its level is fully configured. Without
        // this check the send job silently drops the invoice and the batch still
        // reports success, so the user believes the reminders went out.
        $textsByLevel = resolve_static(PaymentReminderText::class, 'query')
            ->with('emailTemplate')
            ->get()
            ->keyBy('reminder_level');

        $unsendable = [];
        $jobs = [];

        foreach ($orders as $order) {
            $level = (int) $order->payment_reminder_current_level + 1;
            $text = $textsByLevel->get($level);

            $reason = match (true) {
                ! $text => __('No reminder text is configured for reminder level :level.', ['level' => $level]),
                ! $text->emailTemplate => __(
                    'The reminder text for reminder level :level has no email template.',
                    ['level' => $level]
                ),
                default => null,
            };

            if ($reason) {
                $unsendable[] = [
                    'id' => $order->getKey(),
                    'invoice_number' => $order->invoice_number,
                    'reminder_level' => $level,
                    'reason' => $reason,
                ];

                continue;
            }

            // One mail per invoice. All sends run as a single monitored batch (even a
            // batch of one) so the user gets the same progress toast as bulk mailing.
            $jobs[] = app(SendPaymentReminderJob::class, [
                'orderId' => $order->getKey(),
                'recipientOverride' => data_get($recipientById, $order->getKey()),
            ]);
        }

        if ($jobs) {
            Bus::monitoredBatch($jobs)
                ->name(__('Payment Reminders'))
                ->allowFailures()
                ->dispatch();
        }

        return [
            'queued' => count($jobs),
            'unsendable' => $unsendable,
        ];
    }
}
