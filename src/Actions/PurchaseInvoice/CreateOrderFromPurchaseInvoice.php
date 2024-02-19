<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\OrderPosition\CreateOrderPosition;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rules\ModelExists;

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
            'id' => [
                'required',
                'integer',
                new ModelExists(PurchaseInvoice::class),
            ],
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
            'contact_id' => [
                'required',
                'integer',
                new ModelExists(Contact::class),
            ],
            'order_type_id' => [
                'required',
                'integer',
                new ModelExists(OrderType::class),
            ],
            'invoice_number' => 'required|string',
            'purchase_invoice_positions' => 'required|array',
            'purchase_invoice_positions.*' => 'required|array',
        ];
    }

    public static function models(): array
    {
        return [PurchaseInvoice::class, Order::class];
    }

    public function performAction(): Order
    {
        $order = CreateOrder::make($this->data)->validate()->execute();

        foreach (data_get($this->data, 'purchase_invoice_positions', []) as $position) {
            CreateOrderPosition::make($position + ['order_id' => $order->id])
                ->validate()
                ->execute();
        }

        $order->calculatePrices()->calculatePaymentState()->save();

        $invoice = $this->purchaseInvoice->getFirstMedia('purchase_invoice')->move($order, 'invoice');

        $this->purchaseInvoice->fill($this->data);
        $this->purchaseInvoice->media_id = $invoice->id;
        $this->purchaseInvoice->order_id = $order->id;
        $this->purchaseInvoice->save();

        $this->purchaseInvoice->delete();

        return $order->fresh();
    }
}
