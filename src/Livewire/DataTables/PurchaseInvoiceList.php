<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Forms\MediaUploadForm;
use FluxErp\Livewire\Forms\PurchaseInvoiceForm;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Currency;
use FluxErp\Models\Media;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PurchaseInvoice;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class PurchaseInvoiceList extends BaseDataTable
{
    use WithFileUploads;

    protected string $model = PurchaseInvoice::class;

    public ?string $includeBefore = 'flux::livewire.accounting.purchase-invoice-list.include-before';

    public array $enabledCols = [
        'url',
        'media.file_name',
        'contact.invoice_address.name',
        'tags.name',
    ];

    public array $formatters = [
        'url' => 'image',
    ];

    public PurchaseInvoiceForm $purchaseInvoiceForm;

    public MediaUploadForm $mediaForm;

    public function mountSupportsCache(): void
    {
        parent::mountSupportsCache();

        if (! $this->userFilters) {
            $this->userFilters = [
                [
                    [
                        'column' => 'order_id',
                        'operator' => 'is null',
                        'value' => null,
                    ],
                ],
            ];
        }
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->with(['media', 'invoice']);
    }

    public function downloadMedia(Media $media): false|BinaryFileResponse
    {
        if (! file_exists($media->getPath())) {
            $this->notification()->error(__('The file does not exist anymore.'));

            return false;
        }

        return response()->download($media->getPath(), $media->file_name);
    }

    protected function getLayout(): string
    {
        return 'tall-datatables::layouts.grid';
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('Upload'))
                ->wireClick('edit'),
        ];
    }

    protected function getViewData(): array
    {
        $purchaseOrderTypes = array_filter(
            OrderTypeEnum::cases(),
            fn (OrderTypeEnum $orderTypeEnum) => $orderTypeEnum->isPurchase()
        );

        return array_merge(
            parent::getViewData(),
            [
                'clients' => resolve_static(Client::class, 'query')->pluck('name', 'id'),
                'currencies' => resolve_static(Currency::class, 'query')->pluck('name', 'id'),
                'orderTypes' => resolve_static(OrderType::class, 'query')
                    ->whereIn('order_type_enum', $purchaseOrderTypes)
                    ->pluck('name', 'id'),
                'paymentTypes' => resolve_static(PaymentType::class, 'query')
                    ->where('is_purchase', true)
                    ->pluck('name', 'id'),
                'vatRates' => resolve_static(VatRate::class, 'query')->pluck('name', 'id'),
            ]
        );
    }

    protected function getRowAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag(
            [
                'wire:click' => <<<'JS'
                    edit(record.id)
                JS,
                'class' => 'cursor-pointer',
            ]
        );
    }

    protected function itemToArray($item): array
    {
        $itemArray = parent::itemToArray($item);

        $media = $item->media->first() ?? $item->invoice;
        if ($media?->hasGeneratedConversion('thumb_400x400')) {
            $itemArray['url'] = $media?->getUrl('thumb_400x400');
        } else {
            $itemArray['url'] = $media?->getUrl();
        }

        $itemArray['media.file_name'] = $media?->file_name;

        return $itemArray;
    }

    #[Renderless]
    public function edit(?PurchaseInvoice $purchaseInvoice = null): void
    {
        $this->purchaseInvoiceForm->reset();
        $this->mediaForm->reset();

        if ($purchaseInvoice->exists) {
            $purchaseInvoice->loadMissing(['purchaseInvoicePositions', 'invoice']);
            $this->purchaseInvoiceForm->fill($purchaseInvoice);
            $this->purchaseInvoiceForm->mediaUrl = $purchaseInvoice->getFirstMediaUrl('purchase_invoice')
                ?: $purchaseInvoice->invoice->getUrl();
            $this->purchaseInvoiceForm->findMostUsedLedgerAccountId();
        }

        $this->js(<<<'JS'
            $openModal('edit-purchase-invoice');
        JS);
    }

    public function save(): bool
    {
        $this->purchaseInvoiceForm->media = $this->mediaForm->uploadedFile[0] ?? null;
        try {
            $this->purchaseInvoiceForm->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function finish(): bool
    {
        try {
            $this->purchaseInvoiceForm->finish();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    public function delete(): bool
    {
        try {
            $this->purchaseInvoiceForm->delete();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function fillFromSelectedContact(Contact $contact): void
    {
        $bankConnection = $contact->contactBankConnections()->latest()->first();
        $this->purchaseInvoiceForm->approval_user_id ??= $contact->approval_user_id;
        $this->purchaseInvoiceForm->payment_type_id ??= $contact->purchase_payment_type_id ?? $contact->payment_type_id;
        $this->purchaseInvoiceForm->currency_id = $contact->currency_id ?? Currency::default()?->id;
        $this->purchaseInvoiceForm->client_id = $contact->client_id;

        $this->purchaseInvoiceForm->lay_out_user_id = null;
        $this->purchaseInvoiceForm->account_holder = $bankConnection?->account_holder;
        $this->purchaseInvoiceForm->bank_name = $bankConnection?->bank_name;
        $this->purchaseInvoiceForm->bic = $bankConnection?->bic;
        $this->purchaseInvoiceForm->iban = $bankConnection?->iban;

        $this->purchaseInvoiceForm->findMostUsedLedgerAccountId();
    }
}
