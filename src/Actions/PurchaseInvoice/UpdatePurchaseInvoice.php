<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdatePurchaseInvoiceRequest;
use FluxErp\Models\Order;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePurchaseInvoice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdatePurchaseInvoiceRequest())->rules();
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

        return $purchaseInvoice->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(PurchaseInvoice::class));

        $this->data = $validator->validate();

        if (
            data_get($this->data, 'invoice_number')
            && data_get($this->data, 'contact_id')
            && data_get($this->data, 'client_id')
        ) {

            if (Order::query()
                ->where('client_id', $this->data['client_id'])
                ->where('invoice_number', $this->data['invoice_number'])
                ->where('contact_id', $this->data['contact_id'])
                ->exists()
            ) {
                throw ValidationException::withMessages([
                    'invoice_number' => [__('validation.unique', ['attribute' => 'invoice_number'])],
                ])->errorBag('createOrder');
            }
        }
    }
}
