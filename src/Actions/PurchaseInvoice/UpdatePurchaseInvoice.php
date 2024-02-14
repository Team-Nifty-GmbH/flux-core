<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\PurchaseInvoicePosition\CreatePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\DeletePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\UpdatePurchaseInvoicePosition;
use FluxErp\Helpers\Helper;
use FluxErp\Http\Requests\UpdatePurchaseInvoiceRequest;
use FluxErp\Models\Order;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Validation\ValidationException;

class UpdatePurchaseInvoice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_silently(UpdatePurchaseInvoiceRequest::class)->rules();
    }

    public static function models(): array
    {
        return [PurchaseInvoice::class];
    }

    public function performAction(): PurchaseInvoice
    {
        $positions = data_get($this->data, 'purchase_invoice_positions');
        $purchaseInvoice = PurchaseInvoice::query()
            ->whereKey($this->data['id'])
            ->first();

        $purchaseInvoice->fill($this->data);
        $purchaseInvoice->save();

        if (! is_null($positions)) {
            Helper::updateRelatedRecords(
                model: $purchaseInvoice,
                related: $positions,
                relation: 'purchaseInvoicePositions',
                foreignKey: 'purchase_invoice_id',
                createAction: CreatePurchaseInvoicePosition::class,
                updateAction: UpdatePurchaseInvoicePosition::class,
                deleteAction: DeletePurchaseInvoicePosition::class
            );
        }

        return $purchaseInvoice->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        $purchaseInvoice = PurchaseInvoice::query()
            ->whereKey($this->data['id'])
            ->first();

        if (
            data_get($this->data, 'invoice_number', $purchaseInvoice->invoice_number)
            && data_get($this->data, 'contact_id', $purchaseInvoice->contact_id)
            && data_get($this->data, 'client_id', $purchaseInvoice->client_id)
        ) {
            if (Order::query()
                ->where('client_id', $this->data['client_id'])
                ->where('invoice_number', $this->data['invoice_number'])
                ->where('contact_id', $this->data['contact_id'])
                ->when($purchaseInvoice->order_id, fn ($query) => $query->whereKeyNot($purchaseInvoice->order_id))
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'invoice_number' => [__('validation.unique', ['attribute' => 'invoice_number'])],
                ])->errorBag('updatePurchaseInvoice');
            }
        }
    }
}
