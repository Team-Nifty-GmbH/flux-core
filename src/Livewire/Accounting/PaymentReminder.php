<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\PaymentReminder\CreatePaymentReminder;
use FluxErp\Actions\Printing;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder as PaymentReminderModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class PaymentReminder extends \FluxErp\Livewire\DataTables\OrderList
{
    public array $enabledCols = [
        'invoice_number',
        'invoice_date',
        'contact.customer_number',
        'address_invoice.name',
        'total_gross_price',
        'balance',
        'commission',
        'payment_reminders.reminder_level',
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
            ->whereHas('paymentType', function (Builder $query) {
                $query->where('is_direct_debit', false)
                    ->where('requires_manual_transfer', true);
            })
            ->where(function (Builder $query) {
                $query
                    ->whereHas(
                        'paymentRuns',
                        fn (Builder $builder) => $builder->whereNotIn('state', ['open', 'successful', 'pending'])
                    )
                    ->orWhereDoesntHave('paymentRuns');
            })
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

                $paymentReminderPdf = Printing::make([
                    'model_type' => app(PaymentReminderModel::class)->getMorphClass(),
                    'model_id' => $paymentReminder->id,
                    'view' => 'payment-reminder',
                ])
                    ->validate()
                    ->execute()
                    ->attachToModel($order);

                $mailMessages[] = [
                    'to' => $order->contact->invoiceAddress->email,
                    'subject' => html_entity_decode($order->orderType->mail_subject) ?:
                        $order->orderType->name . ' ' . $order->order_number,
                    'attachments' => [
                        [
                            'name' => $paymentReminderPdf->file_name,
                            'id' => $paymentReminderPdf->id,
                        ],
                        [
                            'name' => $order->invoice->file_name,
                            'id' => $order->invoice->id,
                        ]
                    ],
                    'html_body' => html_entity_decode($order->orderType->mail_body),
                    'blade_parameters_serialized' => true,
                    'blade_parameters' => serialize(['order' => $order]),
                    'communicatable_type' => app(Order::class)->getMorphClass(),
                    'communicatable_id' => $order->id,
                ];
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $this->loadData();
    }
}
