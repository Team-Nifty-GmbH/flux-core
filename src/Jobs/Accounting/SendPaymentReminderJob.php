<?php

namespace FluxErp\Jobs\Accounting;

use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Actions\PaymentReminder\CreatePaymentReminder;
use FluxErp\Actions\PaymentReminder\MarkPaymentReminderSent;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use FluxErp\Models\PaymentReminderText;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendPaymentReminderJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $orderId,
        public ?string $recipientOverride = null,
    ) {}

    public function handle(): void
    {
        $order = resolve_static(Order::class, 'query')
            ->whereKey($this->orderId)
            ->with(['contact'])
            ->wherePaymentReminderEligible()
            ->first();

        if (! $order) {
            return;
        }

        $reminder = CreatePaymentReminder::make([
            'order_id' => $order->getKey(),
            'reminder_level' => (int) $order->payment_reminder_current_level + 1,
            'mark_as_sent' => false,
        ])
            ->validate()
            ->execute();

        // Only send when a reminder text exists for exactly this level; otherwise
        // keep the invoice in the run but do not send anything.
        $text = resolve_static(PaymentReminderText::class, 'query')
            ->where('reminder_level', $reminder->reminder_level)
            ->first();

        if (! $text) {
            $this->skip($reminder, $order, 'No payment reminder text for level ' . $reminder->reminder_level);

            return;
        }

        if (! $text->emailTemplate) {
            $this->skip($reminder, $order, 'Missing payment reminder template');

            return;
        }

        $attachments = [
            [
                'model_type' => $reminder->getMorphClass(),
                'model_id' => $reminder->getKey(),
                'view' => 'payment-reminder',
                'attach_relation' => 'order',
            ],
        ];

        if ($invoicePdf = $reminder->order->invoice()) {
            $attachments[] = [
                'id' => $invoicePdf->getKey(),
                'name' => $invoicePdf->file_name,
            ];
        }

        $address = $order->resolveMailablePaymentReminderAddress();

        $to = $text->mail_to ?? [];
        if ($email = $this->recipientOverride ?: $address?->email_primary) {
            $to[] = $email;
        }

        $to = array_values(array_unique(array_filter($to)));
        $cc = array_values(array_unique(array_filter($text->mail_cc ?? [])));

        if (! $to) {
            $this->abort($reminder, $order, 'No recipient address');

            return;
        }

        $result = SendMail::make([
            'template_id' => $text->email_template_id,
            'to' => $to,
            'cc' => $cc ?: null,
            'attachments' => $attachments,
            'blade_parameters' => [
                'order' => $order,
                'contact' => $order->contact,
                'paymentReminder' => $reminder,
                'paymentReminders' => collect([$reminder]),
                'orders' => collect([$order]),
            ],
            'communicatables' => [
                [
                    'model_type' => $order->getMorphClass(),
                    'model_id' => $order->getKey(),
                ],
            ],
        ])
            ->validate()
            ->execute();

        if (! data_get($result, 'success', false)) {
            $this->abort(
                $reminder,
                $order,
                data_get($result, 'error') ?? data_get($result, 'message'),
                $to,
            );

            return;
        }

        MarkPaymentReminderSent::make(['id' => $reminder->getKey()])
            ->validate()
            ->execute();
    }

    protected function abort(PaymentReminder $reminder, Order $order, ?string $reason, ?array $to = null): void
    {
        $this->cleanup(
            $reminder,
            $order,
            'payment_reminder_send_failed',
            'Payment reminder send failed',
            $reason,
            $to,
        );
    }

    protected function cleanup(
        PaymentReminder $reminder,
        Order $order,
        string $event,
        string $message,
        ?string $reason,
        ?array $to = null
    ): void {
        PaymentReminder::withoutEvents(fn () => $reminder->forceDelete());

        activity()
            ->event($event)
            ->byAnonymous()
            ->performedOn($order)
            ->withProperties(array_filter([
                'error' => $reason,
                'to' => $to,
            ]))
            ->log($message);
    }

    protected function skip(PaymentReminder $reminder, Order $order, ?string $reason): void
    {
        $this->cleanup(
            $reminder,
            $order,
            'payment_reminder_skipped',
            'Payment reminder skipped',
            $reason,
        );
    }
}
