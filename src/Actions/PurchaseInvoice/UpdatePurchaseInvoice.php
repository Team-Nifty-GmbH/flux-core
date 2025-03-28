<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\PurchaseInvoicePosition\CreatePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\DeletePurchaseInvoicePosition;
use FluxErp\Actions\PurchaseInvoicePosition\UpdatePurchaseInvoicePosition;
use FluxErp\Helpers\Helper;
use FluxErp\Models\Order;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\PurchaseInvoice\UpdatePurchaseInvoiceRuleset;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdatePurchaseInvoice extends FluxAction
{
    public static function models(): array
    {
        return [PurchaseInvoice::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdatePurchaseInvoiceRuleset::class;
    }

    public function performAction(): PurchaseInvoice
    {
        $tags = Arr::pull($this->data, 'tags');
        $positions = data_get($this->data, 'purchase_invoice_positions');
        $purchaseInvoice = resolve_static(PurchaseInvoice::class, 'query')
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

        if (! is_null($tags)) {
            $purchaseInvoice->syncTags(
                resolve_static(Tag::class, 'query')->whereIntegerInRaw('id', $tags)->get()
            );
        }

        return $purchaseInvoice->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $purchaseInvoice = resolve_static(PurchaseInvoice::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $invoiceNumber = data_get($this->data, 'invoice_number', $purchaseInvoice->invoice_number);
        $contactId = data_get($this->data, 'contact_id', $purchaseInvoice->contact_id);
        $clientId = data_get($this->data, 'client_id', $purchaseInvoice->client_id);

        if ($invoiceNumber && $contactId && $clientId) {
            if (resolve_static(Order::class, 'query')
                ->where('client_id', $clientId)
                ->where('invoice_number', $invoiceNumber)
                ->where('contact_id', $contactId)
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
