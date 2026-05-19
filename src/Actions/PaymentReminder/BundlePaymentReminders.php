<?php

namespace FluxErp\Actions\PaymentReminder;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Actions\Printing;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Rulesets\PaymentReminder\BundlePaymentRemindersRuleset;
use Illuminate\Support\Collection;
use Throwable;

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
            ->with(['contact', 'orderType:id,order_type_enum'])
            ->whereNotNull('invoice_number')
            ->where('is_locked', true)
            ->where('balance', '!=', 0)
            ->whereHasMailablePaymentReminderAddress()
            ->get()
            ->filter(fn (Order $order) => ! $order->orderType->order_type_enum->isPurchase()
                && $order->orderType->order_type_enum->multiplier() === 1
            );

        $groups = $orders->groupBy(
            fn (Order $order) => $order->contact_id . '-' . ((int) $order->payment_reminder_current_level + 1)
        );

        $results = [
            'sent_groups' => 0,
            'failed_groups' => 0,
            'sent_reminders' => collect(),
        ];

        foreach ($groups as $group) {
            $sent = $this->sendGroup($group);

            if ($sent) {
                $results['sent_groups']++;
                $results['sent_reminders'] = $results['sent_reminders']->merge($sent);
            } else {
                $results['failed_groups']++;
            }
        }

        return $results;
    }

    protected function sendGroup(Collection $orders): ?Collection
    {
        $reminders = collect();

        try {
            foreach ($orders as $order) {
                $reminder = app(PaymentReminder::class, [
                    'attributes' => ['order_id' => $order->getKey()],
                ]);
                $reminder->save();
                $reminders->push($reminder);
            }
        } catch (Throwable $e) {
            return $this->abortGroup($reminders, $orders, $e->getMessage());
        }

        $attachments = collect();
        $reminderText = $reminders->first()?->getPaymentReminderText();

        if (! $reminderText?->emailTemplate) {
            return $this->abortGroup($reminders, $orders, 'Missing payment reminder template');
        }

        foreach ($reminders as $reminder) {
            $pdf = Printing::make([
                'model_type' => $reminder->getMorphClass(),
                'model_id' => $reminder->getKey(),
                'view' => 'payment-reminder',
                'html' => false,
                'preview' => false,
                'attach_relation' => 'order',
            ])
                ->validate()
                ->execute();

            if (! $pdf) {
                return $this->abortGroup($reminders, $orders, 'PDF generation failed');
            }

            $attachments->push([
                'id' => $pdf->getKey(),
                'name' => $pdf->file_name,
            ]);

            if ($invoicePdf = $reminder->order->invoice()) {
                $attachments->push([
                    'id' => $invoicePdf->getKey(),
                    'name' => $invoicePdf->file_name,
                ]);
            }
        }

        $firstOrder = $orders->first();
        $address = $firstOrder->resolveMailablePaymentReminderAddress();

        $to = $reminderText->mail_to ?? [];
        if ($email = $address?->email_primary) {
            $to[] = $email;
        }

        $cc = $reminderText->mail_cc ?? [];
        $to = array_values(array_unique(array_filter($to)));
        $cc = array_values(array_unique(array_filter($cc)));

        if (! $to) {
            return $this->abortGroup($reminders, $orders, 'No recipient address');
        }

        $result = SendMail::make([
            'template_id' => $reminderText->email_template_id,
            'to' => $to,
            'cc' => $cc ?: null,
            'attachments' => $attachments->all(),
            'blade_parameters' => [
                'order' => $firstOrder,
                'contact' => $firstOrder->contact,
                'paymentReminder' => $reminders->first(),
                'paymentReminders' => $reminders,
                'orders' => $orders,
            ],
            'communicatables' => $orders->map(fn (Order $order) => [
                'model_type' => $order->getMorphClass(),
                'model_id' => $order->getKey(),
            ])->all(),
        ])
            ->validate()
            ->execute();

        if (! data_get($result, 'success', false)) {
            return $this->abortGroup(
                $reminders,
                $orders,
                data_get($result, 'error') ?? data_get($result, 'message'),
                $to,
            );
        }

        $reminders->each(function (PaymentReminder $reminder): void {
            MarkPaymentReminderSent::make(['id' => $reminder->getKey()])
                ->validate()
                ->execute();
        });

        return $reminders;
    }

    protected function abortGroup(
        Collection $reminders,
        Collection $orders,
        ?string $reason,
        ?array $to = null,
    ): ?Collection {
        $reminders->each(fn (PaymentReminder $reminder) => $reminder->forceDelete());

        $orders->each(function (Order $order) use ($reason, $to): void {
            activity()
                ->event('payment_reminder_send_failed')
                ->byAnonymous()
                ->performedOn($order)
                ->withProperties(array_filter([
                    'error' => $reason,
                    'to' => $to,
                ]))
                ->log('Payment reminder send failed');
        });

        return null;
    }
}
