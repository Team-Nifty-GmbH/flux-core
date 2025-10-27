<?php

namespace FluxErp\Jobs\Accounting;

use Cron\CronExpression;
use Exception;
use FluxErp\Actions\MailMessage\SendMail;
use FluxErp\Actions\PaymentReminder\CreatePaymentReminder;
use FluxErp\Actions\Printing;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Enums\RepeatableTypeEnum;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
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

    public static function repeatableType(): RepeatableTypeEnum
    {
        return RepeatableTypeEnum::Invokable;
    }

    public function handle(): void
    {
        if (! app(AccountingSettings::class)->auto_send_reminders) {
            return;
        }

        resolve_static(Order::class, 'query')
            ->when($this->orderIds, fn (Builder $query) => $query->whereKey($this->orderIds))
            ->with(['orderType:id,order_type_enum', 'contact.mainAddress', 'contact.invoiceAddress'])
            ->whereNotNull('invoice_number')
            ->where('is_locked', true)
            ->where('balance', '!=', 0)
            ->whereDate('payment_reminder_next_date', '<=', now()->toDateString())
            ->whereHas('contact', function (Builder $query): void {
                $query->whereHas('mainAddress', function (Builder $query): void {
                    $query->whereNotNull('email_primary');
                });
            })
            ->cursor()
            ->each(function (Order $order): void {
                if ($order->orderType->order_type_enum->isPurchase()
                    || $order->orderType->order_type_enum->multiplier() != 1
                ) {
                    return;
                }

                $this->processOrder($order);
            });
    }

    protected function processOrder(Order $order): void
    {
        try {
            $paymentReminder = CreatePaymentReminder::make([
                'order_id' => $order->getKey(),
            ])
                ->validate()
                ->execute();

            $this->sendPaymentReminderEmail($paymentReminder);
        } catch (Exception $e) {
            logger()->error('Failed to process payment reminder for order ' . $order->getKey(), [
                'error' => $e->getMessage(),
                'order_id' => $order->getKey(),
            ]);
        }
    }

    protected function sendPaymentReminderEmail(PaymentReminder $paymentReminder): void
    {
        /** @var Order $order */
        $order = $paymentReminder->order;

        if (! $order->contact) {
            return;
        }

        $paymentReminderText = $paymentReminder->getPaymentReminderText();

        if (! $paymentReminderText?->emailTemplate) {
            return;
        }

        $paymentReminderPdf = Printing::make([
            'model_type' => $paymentReminder->getMorphClass(),
            'model_id' => $paymentReminder->getKey(),
            'view' => 'payment-reminder',
            'html' => false,
            'preview' => false,
            'attach_relation' => 'order',
        ])
            ->validate()
            ->execute();

        if (! $paymentReminderPdf) {
            logger()->error('Failed to generate PDF for payment reminder ' . $paymentReminder->getKey());

            return;
        }

        $invoicePdf = $order->invoice();

        $address = $order->contact->invoiceAddress ?? $order->contact->mainAddress;
        $to = $paymentReminderText->mail_to ?? [];
        $to[] = $address?->email_primary ?? $order->contact->mainAddress?->email_primary;
        $cc = $paymentReminderText->mail_cc ?? [];
        $to = array_values(array_unique(array_filter($to)));
        $cc = array_values(array_unique(array_filter($cc)));

        if (! $to) {
            return;
        }

        $attachments = [
            [
                'id' => $paymentReminderPdf->getKey(),
                'name' => $paymentReminderPdf->file_name,
            ],
        ];

        if ($invoicePdf) {
            $attachments[] = [
                'id' => $invoicePdf->getKey(),
                'name' => $invoicePdf->file_name,
            ];
        }

        $result = SendMail::make([
            'template_id' => $paymentReminderText->email_template_id,
            'to' => $to,
            'cc' => $cc ?: null,
            'attachments' => $attachments,
            'blade_parameters' => [
                'order' => $order,
                'contact' => $order->contact,
                'paymentReminder' => $paymentReminder,
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
            activity()
                ->event('payment_reminder_email_failed')
                ->performedOn($order)
                ->withProperties([
                    'error' => data_get($result, 'error'),
                    'message' => data_get($result, 'message'),
                    'payment_reminder_id' => $paymentReminder->getKey(),
                    'to' => $to,
                ])
                ->log('Payment reminder email failed');
        }
    }
}
