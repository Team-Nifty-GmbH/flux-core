<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Forms\MediaForm;
use FluxErp\Livewire\Forms\PurchaseInvoiceForm;
use FluxErp\Models\Client;
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
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class PurchaseInvoiceList extends DataTable
{
    use WithFileUploads;

    protected string $model = PurchaseInvoice::class;

    public ?string $includeBefore = 'flux::livewire.accounting.purchase-invoice-list.include-before';

    public array $enabledCols = [
        'url',
        'media.file_name',
    ];

    public array $formatters = [
        'url' => 'image',
    ];

    public PurchaseInvoiceForm $purchaseInvoiceForm;

    public MediaForm $mediaForm;

    public function mount(): void
    {
        parent::mount();

        if (! $this->userFilters) {
            $this->userFilters = [
                [
                    'column' => 'order_id',
                    'operator' => 'is null',
                    'value' => null,
                ],
            ];
        }
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->with('media');
    }

    public function downloadMedia(Media $media): false|BinaryFileResponse
    {
        if (! file_exists($media->getPath())) {
            $this->notification()->error(__('The file does not exist anymore.'));

            return false;
        }

        return response()->download($media->getPath(), $media->file_name);
    }

    public function getLayout(): string
    {
        return 'tall-datatables::layouts.grid';
    }

    public function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->color('primary')
                ->label(__('Upload'))
                ->wireClick('edit'),
        ];
    }

    public function getViewData(): array
    {
        $purchaseOrderTypes = array_filter(
            OrderTypeEnum::cases(),
            fn (OrderTypeEnum $orderTypeEnum) => $orderTypeEnum->isPurchase()
        );

        return array_merge(
            parent::getViewData(),
            [
                'clients' => app(Client::class)->query()->pluck('name', 'id'),
                'currencies' => app(Currency::class)->query()->pluck('name', 'id'),
                'orderTypes' => app(OrderType::class)->query()
                    ->whereIn('order_type_enum', $purchaseOrderTypes)
                    ->pluck('name', 'id'),
                'paymentTypes' => app(PaymentType::class)->query()->where('is_purchase', true)->pluck('name', 'id'),
                'vatRates' => app(VatRate::class)->query()->pluck('name', 'id'),
            ]
        );
    }

    public function getRowAttributes(): ComponentAttributeBag
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

    public function itemToArray($item): array
    {
        $itemArray = parent::itemToArray($item);
        if ($item->media->first()?->hasGeneratedConversion('thumb_400x400')) {
            $itemArray['url'] = $item->media->first()?->getUrl('thumb_400x400');
        } else {
            $itemArray['url'] = $item->media->first()?->getUrl();
        }

        return $itemArray;
    }

    public function edit(?PurchaseInvoice $purchaseInvoice = null): void
    {
        $this->purchaseInvoiceForm->reset();
        $this->mediaForm->reset();

        if ($purchaseInvoice) {
            $purchaseInvoice->loadMissing('purchaseInvoicePositions');
            $this->purchaseInvoiceForm->fill($purchaseInvoice);
            $this->purchaseInvoiceForm->mediaUrl = $purchaseInvoice->getFirstMediaUrl('purchase_invoice');
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
}
