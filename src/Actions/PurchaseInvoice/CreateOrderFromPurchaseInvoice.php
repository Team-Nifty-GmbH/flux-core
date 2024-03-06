<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Models\Order;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rulesets\PurchaseInvoice\CreateOrderFromPurchaseInvoiceRuleset;

class CreateOrderFromPurchaseInvoice extends FluxAction
{
    public ?PurchaseInvoice $purchaseInvoice;

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateOrderFromPurchaseInvoiceRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [PurchaseInvoice::class, Order::class];
    }

    public function performAction(): Order
    {
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
        $this->purchaseInvoice = app(PurchaseInvoice::class)->query()
            ->whereKey($this->data['id'] ?? null)
            ->with('purchaseInvoicePositions')
            ->first();
        $this->data = array_merge($this->purchaseInvoice?->toArray() ?? [], $this->data);
        $this->data['invoice_date'] ??= now()->toDateString();
    }
}
