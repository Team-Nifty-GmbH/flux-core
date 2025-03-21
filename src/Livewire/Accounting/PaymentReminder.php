<?php

namespace FluxErp\Livewire\Accounting;

use FluxErp\Actions\Order\UpdateLockedOrder;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\PaymentReminder\CreatePaymentReminder;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Livewire\DataTables\OrderList;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentReminder as PaymentReminderModel;
use FluxErp\Models\PaymentReminderText;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\Traits\Livewire\CreatesDocuments;
use FluxErp\View\Printing\PaymentReminder\PaymentReminderView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Laravel\SerializableClosure\SerializableClosure;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class PaymentReminder extends OrderList
{
    use CreatesDocuments;

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

    protected ?string $includeBefore = 'flux::livewire.create-documents-modal';

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->color('indigo')
                ->text(__('Create Payment Reminder'))
                ->wireClick('openCreateDocumentsModal'),
            DataTableButton::make()
                ->text(__('Mark as paid'))
                ->when(fn () => resolve_static(UpdateOrder::class, 'canPerformAction', [false]))
                ->wireClick('markAsPaid'),
        ];
    }

    public function createDocuments(): null|MediaStream|Media
    {
        $orders = $this->getSelectedModels();

        try {
            resolve_static(CreatePaymentReminder::class, 'canPerformAction', [true]);
        } catch (UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
        }

        $documents = collect();
        $reminderTextExists = [];
        foreach ($orders as $order) {
            try {
                $paymentReminder = CreatePaymentReminder::make([
                    'order_id' => $order->id,
                ])
                    ->validate()
                    ->execute();

                $reminderTextExists[$paymentReminder->reminder_level] ??=
                    resolve_static(PaymentReminderText::class, 'query')
                        ->where('reminder_level', '<=', $paymentReminder->reminder_level)
                        ->orderBy('reminder_level', 'desc')
                        ->exists();

                if (! data_get($reminderTextExists, $paymentReminder->reminder_level)) {
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

                $documents->push($paymentReminder);
            } catch (ValidationException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $response = $this->createDocumentFromItems($documents, true);

        $this->loadData();
        $this->reset('selected');

        return $response;
    }

    public function markAsPaid(): void
    {
        foreach ($this->getSelectedValues() as $selectedValue) {
            try {
                UpdateLockedOrder::make([
                    'id' => $selectedValue,
                    'payment_state' => Paid::class,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (UnauthorizedException|ValidationException $e) {
                exception_to_notifications($e, $this);
            }
        }

        $this->loadData();
    }

    protected function getAttachments(OffersPrinting $item): array
    {
        return [
            'id' => $item->order->invoice()->id,
            'name' => $item->order->invoice()->file_name,
        ];
    }

    protected function getBladeParameters(OffersPrinting $item): array|SerializableClosure|null
    {
        return new SerializableClosure(
            fn () => [
                'paymentReminder' => resolve_static(PaymentReminderModel::class, 'query')
                    ->whereKey($item->getKey())
                    ->first(),
            ]
        );
    }

    protected function getBuilder(Builder $builder): Builder
    {
        $orderTypes = resolve_static(OrderType::class, 'query')
            ->where('is_active', true)
            ->get(['id', 'order_type_enum'])
            ->filter(fn (OrderType $orderType) => ! $orderType->order_type_enum->isPurchase()
                && $orderType->order_type_enum->multiplier() > 0
            )
            ->pluck('id');

        return $builder
            ->whereRelation('paymentType', 'is_direct_debit', false)
            ->where('balance', '>', 0)
            ->whereNotState('payment_state', Paid::class)
            ->whereNotNull('invoice_number')
            ->whereIntegerInRaw('order_type_id', $orderTypes);
    }

    protected function getCc(OffersPrinting $item): array
    {
        return Arr::wrap($item->getPaymentReminderText()?->mail_cc);
    }

    protected function getCommunicatableId(OffersPrinting $item): int
    {
        return $item->order_id;
    }

    protected function getCommunicatableType(OffersPrinting $item): string
    {
        return morph_alias(Order::class);
    }

    protected function getCreateAttachmentArray(OffersPrinting $item, string $view): array
    {
        return [
            'model_type' => $item->getMorphClass(),
            'model_id' => $item->getKey(),
            'view' => $view,
            'name' => __($view),
            'attach_relation' => 'order',
        ];
    }

    protected function getHtmlBody(OffersPrinting $item): string
    {
        return html_entity_decode(
            $item->getPaymentReminderText()?->mail_body
                    ?? $item->getPaymentReminderText()?->reminder_body
                    ?? ''
        );
    }

    protected function getPrintLayouts(): array
    {
        return [
            'payment-reminder' => PaymentReminderView::class,
        ];
    }

    protected function getSubject(OffersPrinting $item): string
    {
        return html_entity_decode($item->getPaymentReminderText()?->mail_subject ?? '') ?:
            __(
                'Payment Reminder :level for invoice :invoice_number',
                [
                    'level' => $item->reminder_level,
                    'invoice_number' => $item->order->invoice_number,
                ]
            );
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        return Arr::wrap($item->getPaymentReminderText()?->mail_to
            ?: $item->order->contact->invoiceAddress->email_primary
        );
    }
}
