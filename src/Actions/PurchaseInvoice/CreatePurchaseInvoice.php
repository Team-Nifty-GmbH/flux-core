<?php

namespace FluxErp\Actions\PurchaseInvoice;

use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Media\UploadMedia;
use FluxErp\Actions\PurchaseInvoicePosition\CreatePurchaseInvoicePosition;
use FluxErp\Models\Client;
use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Rulesets\PurchaseInvoice\CreatePurchaseInvoiceRuleset;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreatePurchaseInvoice extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreatePurchaseInvoiceRuleset::class, 'getRules');
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

        if (data_get($file, 'id')) {
            $media = resolve_static(Media::class, 'query')
                ->whereKey(data_get($file, 'id'))
                ->first()
                ->copy($purchaseInvoice, 'purchase_invoice');
        } else {
            $media = UploadMedia::make([
                'model_type' => app(PurchaseInvoice::class)->getMorphClass(),
                'model_id' => $purchaseInvoice->id,
                'media' => $file,
                'collection_name' => 'purchase_invoice',
            ])->validate()->execute();
        }

        $purchaseInvoice->media_id = $media->id;
        $purchaseInvoice->save();

        return $purchaseInvoice->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['client_id'] ??= resolve_static(Client::class, 'default')->id;
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $media = data_get($this->data, 'media');
        $filePath = match (true) {
            is_string($media) && is_file($media) => $media,
            is_a($media, \SplFileInfo::class) => $media->getRealPath(),
            (bool) data_get($media, 'id') => resolve_static(Media::class, 'query')
                ->whereKey(data_get($media, 'id'))
                ->first()
                ?->getPath(),
            default => null,
        };

        try {
            $this->data['hash'] = $filePath ? md5_file($filePath) : null;

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
            if (resolve_static(Order::class, 'query')
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
