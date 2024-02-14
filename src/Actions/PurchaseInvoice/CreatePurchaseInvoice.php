<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Actions\PurchaseInvoicePosition\CreatePurchaseInvoicePosition;
use FluxErp\Http\Requests\CreatePurchaseInvoiceRequest;
use FluxErp\Models\Order;
use FluxErp\Models\PurchaseInvoice;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreatePurchaseInvoice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_silently(CreatePurchaseInvoiceRequest::class)->rules();
    }

    public static function models(): array
    {
        return [PurchaseInvoice::class];
    }

    public function performAction(): PurchaseInvoice
    {
        $file = Arr::pull($this->data, 'media');
        $positions = Arr::pull($this->data, 'purchase_invoice_positions', []);
        $this->data['invoice_date'] = data_get($this->data, 'invoice_date') ?? now()->format('Y-m-d');

        $purchaseInvoice = app(PurchaseInvoice::class, ['attributes' => $this->data]);
        $purchaseInvoice->save();

        foreach ($positions as $position) {
            CreatePurchaseInvoicePosition::make($position + ['purchase_invoice_id' => $purchaseInvoice->id])
                ->validate()
                ->execute();
        }

        $media = UploadMedia::make([
            'model_type' => PurchaseInvoice::class,
            'model_id' => $purchaseInvoice->id,
            'media' => $file,
            'collection_name' => 'purchase_invoice',
        ])->validate()->execute();

        $purchaseInvoice->media_id = $media->id;
        $purchaseInvoice->save();

        return $purchaseInvoice->fresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        $errors = [];
        try {
            $this->data['hash'] = md5_file(data_get($this->data, 'media')->getRealPath());

            Validator::make($this->data, ['hash' => 'required|string|unique:purchase_invoices,hash'])
                ->validate();
        } catch (\Exception|ValidationException $e) {
            if ($e instanceof ValidationException) {
                $errors += $e->errors();
            } else {
                $errors += [
                    'media' => ['media is no valid file'],
                ];
            }
        }

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
                $errors += [
                    'invoice_number' => [__('validation.unique', ['attribute' => 'invoice_number'])],
                ];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('createPurchaseInvoice');
        }
    }
}
