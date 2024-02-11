<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Models\Order;
use FluxErp\Models\PurchaseInvoice;

class CreateOrderFromPurchaseInvoice extends FluxAction
{
    public ?PurchaseInvoice $purchaseInvoice;

    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->purchaseInvoice = PurchaseInvoice::query()
            ->with('purchaseInvoicePositions')
            ->whereKey($this->data['id'])
            ->first();
        $this->data = array_merge($this->purchaseInvoice?->toArray() ?? [], $this->data);

        $this->rules = [
            'id' => 'required|integer|exists:purchase_invoices,id,deleted_at,NULL',
            'client_id' => 'required|exists:clients,id,deleted_at,NULL',
            'contact_id' => 'required|exists:contacts,id,deleted_at,NULL',
            'order_type_id' => 'required|exists:order_types,id,deleted_at,NULL',
            'invoice_number' => 'required|string',
            'purchase_invoice_positions' => 'required|array',
            'purchase_invoice_positions.*' => 'required|array',
        ];
    }

    public static function models(): array
    {
        return [PurchaseInvoice::class, Order::class];
    }

    public function performAction(): mixed
    {
        $order = CreateOrder::make($this->purchaseInvoice->toArray())->validate()->execute();

        foreach ($this->purchaseInvoice->purchaseInvoicePositions as $position) {
            CreateOrderPosition::make($position->toArray() + ['order_id' => $order->id])
                ->validate()
                ->execute();
        }

        $order->calculatePrices()->calculatePaymentState()->save();

        $this->purchaseInvoice->getFirstMedia('purchase_invoice')->move($order, 'invoice');

        $this->purchaseInvoice->order_id = $order->id;
        $this->purchaseInvoice->save();

        $this->purchaseInvoice->delete();

        return $this->purchaseInvoice->fresh();
    }
}
