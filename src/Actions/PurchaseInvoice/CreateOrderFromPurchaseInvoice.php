<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\ContactBankConnection\CreateContactBankConnection;
use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rulesets\PurchaseInvoice\CreateOrderFromPurchaseInvoiceRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateOrderFromPurchaseInvoice extends FluxAction
{
    public ?PurchaseInvoice $purchaseInvoice;

    public static function getRulesets(): string|array
    {
        return CreateOrderFromPurchaseInvoiceRuleset::class;
    }

    public static function models(): array
    {
        return [PurchaseInvoice::class, Order::class];
    }

    public function performAction(): Order
    {
        $layOutUserId = Arr::pull($this->data, 'lay_out_user_id');

        if (
            ! $layOutUserId
            && data_get($this->data, 'iban')
            && resolve_static(ContactBankConnection::class, 'query')
                ->where('contact_id', $this->data['contact_id'])
                ->where('iban', data_get($this->data, 'iban'))
                ->doesntExist()
        ) {
            CreateContactBankConnection::make($this->data)
                ->validate()
                ->execute();
        }

        $order = CreateOrder::make($this->data)->validate()->execute();

        foreach (data_get($this->data, 'purchase_invoice_positions', []) as $position) {
            CreateOrderPosition::make(
                array_merge(
                    $position,
                    [
                        'order_id' => $order->id,
                        'is_net' => data_get($this->data, 'is_net', true),
                    ]
                )
            )
                ->validate()
                ->execute();
        }

        $order->calculatePrices()
            ->calculatePaymentState()
            ->save();

        $invoice = $this->purchaseInvoice->getFirstMedia('purchase_invoice')->move($order, 'invoice');

        $this->purchaseInvoice->fill($this->data);
        $this->purchaseInvoice->media_id = $invoice->id;
        $this->purchaseInvoice->order_id = $order->id;
        $this->purchaseInvoice->save();

        return $order->fresh();
    }

    public function prepareForValidation(): void
    {
        $this->purchaseInvoice = resolve_static(PurchaseInvoice::class, 'query')
            ->whereKey($this->data['id'] ?? null)
            ->with('purchaseInvoicePositions')
            ->first();
        $this->data = array_merge($this->purchaseInvoice?->toArray() ?? [], $this->data);
        $this->data['invoice_date'] ??= now()->toDateString();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (
            ! data_get($this->data, 'iban')
            && resolve_static(PaymentType::class, 'query')
                ->whereKey($this->data['payment_type_id'])
                ->value('requires_manual_transfer')
            && resolve_static(ContactBankConnection::class, 'query')
                ->where('contact_id', $this->data['contact_id'])
                ->doesntExist()
        ) {
            throw ValidationException::withMessages([
                'iban' => ['iban' => __('validation.required', ['attribute' => 'IBAN'])],
            ])->errorBag('createOrderFromPurchaseInvoice');
        }
    }
}
