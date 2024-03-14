<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\PaymentReminder\CreatePaymentReminder;
use FluxErp\Actions\Printing;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder as PaymentReminderModel;
use FluxErp\Models\PaymentReminderText;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class PaymentReminder extends \FluxErp\Livewire\DataTables\OrderList
{
    public array $enabledCols = [
        'payment_reminder_current_level',
        'payment_reminder_next_date',
        'invoice_date',
        'invoice_number',
        'contact.customer_number',
        'address_invoice.name',
        'total_gross_price',
        'balance',
        'commission',
    ];

    public function getBuilder(Builder $builder): Builder
    {
        $orderTypes = app(OrderType::class)->query()
            ->where('is_active', true)
            ->get(['id', 'order_type_enum'])
            ->filter(fn (OrderType $orderType) => ! $orderType->order_type_enum->isPurchase()
                && $orderType->order_type_enum->multiplier() > 0
            )
            ->pluck('id');

        return $builder
            ->whereRelation('paymentType', 'is_direct_debit', false)
            ->where('balance', '>', 0)
            ->whereNotNull('invoice_number')
            ->whereIntegerInRaw('order_type_id', $orderTypes);
    }

    public function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('Create Payment Reminder'))
                ->wireClick('createNextPaymentReminder'),
        ];
    }

    public function createNextPaymentReminder(): void
    {
        $orders = $this->getSelectedModels();

        $mailMessages = [];
        foreach ($orders as $order) {
            try {
                $paymentReminder = CreatePaymentReminder::make([
                    'order_id' => $order->id,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();

                $paymentReminderText = app(PaymentReminderText::class)
                    ->where('reminder_level', '<=', $paymentReminder->reminder_level)
                    ->orderBy('reminder_level', 'desc')
                    ->first();

                if (! $paymentReminderText) {
                    $this->notification()
                        ->error(
                            __(
                                'No payment reminder text found for level :level',
                                [
                                    'level' => $paymentReminder->reminder_level,
                                ]
                            )
                        );
                    $paymentReminder->forceDelete();

                    continue;
                }

                $paymentReminderPdf = Printing::make([
                    'model_type' => app(PaymentReminderModel::class)->getMorphClass(),
                    'model_id' => $paymentReminder->id,
                    'view' => 'payment-reminder',
                ])
                    ->validate()
                    ->execute()
                    ->attachToModel($order);

                $mailMessages[] = [
                    'to' => [$paymentReminderText->mail_to ?? $order->contact->invoiceAddress->email],
                    'cc' => $paymentReminderText->mail_cc,
                    'subject' => html_entity_decode($paymentReminderText->mail_subject) ?:
                        __(
                            'Payment Reminder :level for invoice :invoice-number',
                            [
                                'level' => $paymentReminder->reminder_level,
                                'invoice-number' => $order->invoice_number,
                            ]
                        ),
                    'attachments' => [
                        [
                            'name' => $paymentReminderPdf->file_name,
                            'id' => $paymentReminderPdf->id,
                        ],
                        [
                            'name' => $order->invoice()->file_name,
                            'id' => $order->invoice()->id,
                        ],
                    ],
                    'html_body' => html_entity_decode(
                        $paymentReminderText->mail_body ?? $paymentReminderText->reminder_body
                    ),
                    'blade_parameters_serialized' => true,
                    'blade_parameters' => serialize(['paymentReminder' => $paymentReminder]),
                    'communicatable_type' => app(Order::class)->getMorphClass(),
                    'communicatable_id' => $order->id,
                ];
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        if ($mailMessages) {
            $sessionKey = 'mail_' . Str::uuid()->toString();
            session()->put($sessionKey, count($mailMessages) > 1 ? $mailMessages : $mailMessages[0]);
            $this->dispatch('createFromSession', key: $sessionKey)->to('edit-mail');
        }

        $this->selected = [];
        $this->loadData();
    }
}
