<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Livewire\Forms\CollectiveOrderForm;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Tenant;
use FluxErp\Traits\Livewire\CreatesDocuments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use Throwable;

class OrderList extends \FluxErp\Livewire\DataTables\OrderList
{
    use CreatesDocuments;

    public ?string $cacheKey = 'order.order-list';

    public ?int $mapLimit = 100;

    public OrderForm $order;

    public CollectiveOrderForm $collectiveOrder;

    public ?int $orderType = null;

    public bool $showMap = false;

    protected ?string $includeBefore = 'flux::livewire.order.order-list';

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Show on Map'))
                ->color('indigo')
                ->icon('globe-alt')
                ->wireClick(<<<'JS'
                    $toggle('showMap', true)
                JS),
            DataTableButton::make()
                ->color('indigo')
                ->text(__('New order'))
                ->icon('plus')
                ->when(resolve_static(CreateOrder::class, 'canPerformAction', [false]))
                ->wireClick('create'),
        ];
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('document-text')
                ->text(__('Create Documents'))
                ->color('indigo')
                ->wireClick('openCreateDocumentsModal'),
            DataTableButton::make()
                ->icon('trash')
                ->text(__('Delete'))
                ->color('red')
                ->when(fn () => resolve_static(DeleteOrder::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Orders')]),
                ]),
            DataTableButton::make()
                ->text(__('Create Collective Order'))
                ->color('indigo')
                ->when(fn () => resolve_static(CreateOrder::class, 'canPerformAction', [false])
                    && (
                        ! $this->orderType || resolve_static(OrderType::class, 'query')
                            ->whereKey($this->orderType)
                            ->where('order_type_enum', OrderTypeEnum::Order->value)
                            ->exists()
                    )
                    && resolve_static(OrderType::class, 'query')
                        ->where(
                            fn (Builder $query) => $query
                                ->where('order_type_enum', OrderTypeEnum::CollectiveOrder->value)
                                ->orWhere('order_type_enum', OrderTypeEnum::SplitOrder->value),
                        )
                        ->where('is_active', true)
                        ->distinct('order_type_enum')
                        ->count() === 2
                )
                ->attributes([
                    'x-cloak',
                    'x-show' => '$wire.selected.length > 1',
                ])
                ->wireClick('openCreateCollectiveOrderModal'),
        ];
    }

    #[Renderless]
    public function create(): void
    {
        $this->order->payment_type_id ??= resolve_static(PaymentType::class, 'default')?->getKey();
        $this->order->price_list_id ??= resolve_static(PriceList::class, 'default')?->getKey();
        $this->order->payment_type_id ??= resolve_static(PaymentType::class, 'default')?->getKey();
        $this->order->language_id ??= resolve_static(Language::class, 'default')?->getKey();
        $this->order->tenant_id ??= resolve_static(Tenant::class, 'default')?->getKey();

        $this->js(<<<'JS'
             $modalOpen('create-order-modal');
        JS);
    }

    #[Renderless]
    public function createDocuments(): void
    {
        $this->createDocumentFromItems($this->getSelectedModels());
        $this->loadData();
        $this->reset('selected');
    }

    #[Renderless]
    public function fetchContactData(): void
    {
        $contact = resolve_static(Contact::class, 'query')
            ->whereKey($this->order->contact_id)
            ->with('invoiceAddress:id,language_id')
            ->first();

        $this->order->tenant_id = $contact->tenant_id ?? $this->order->tenant_id;
        $this->order->agent_id = $contact->agent_id ?? $this->order->agent_id;
        $this->order->language_id = $contact->invoiceAddress?->language_id;
        $this->order->address_invoice_id = $contact->address_invoice_id;
        $this->order->address_delivery_id = $contact->address_delivery_id;
        $this->order->price_list_id = $contact->price_list_id ?? $this->order->price_list_id;
        $this->order->payment_type_id = $contact->payment_type_id ?? $this->order->payment_type_id;
        $this->order->address_invoice_id = $contact->invoice_address_id ?? $this->order->address_invoice_id;
        $this->order->address_delivery_id = $contact->delivery_address_id ?? $this->order->address_delivery_id;
    }

    #[Renderless]
    public function openCreateCollectiveOrderModal(): void
    {
        $selected = $this->getSelectedModelsQuery()
            ->whereNull('invoice_number')
            ->whereDoesntHave('children')
            ->whereRelation('orderPositions', 'total_net_price', '>', 0)
            ->whereHas(
                'orderType',
                fn (Builder $query) => $query
                    ->where('order_type_enum', OrderTypeEnum::Order->value)
                    ->where('is_active', true)
            )
            ->get(['id', 'address_invoice_id', 'order_type_id'])
            ->mapToGroups(fn (Model $item) => [$item->address_invoice_id => $item])
            ->map(fn (Collection|Model $item, int $key) => ['address_invoice_id' => $key, 'orders' => $item->toArray()])
            ->values()
            ->toArray();

        // If no suitable orders are found return toast message about it.
        if (! $selected) {
            $this->toast()
                ->error(__('No suitable orders found.'))
                ->send();

            return;
        }

        $this->collectiveOrder->reset();
        $this->collectiveOrder->orders = $selected;

        $this->js(<<<'JS'
             $modalOpen('create-collective-order-modal');
        JS);
    }

    public function save(): ?false
    {
        try {
            $this->order->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->redirect(route('orders.id', $this->order->id), true);

        return null;
    }

    #[Renderless]
    public function createCollectiveOrders(): void
    {
        try {
            $this->collectiveOrder->create();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();

        $this->js(<<<'JS'
             $modalClose('create-collective-order-modal');
        JS);
    }

    #[Renderless]
    public function getOrdersWithoutCoordinatesCount(): int
    {
        return $this->buildSearch()
            ->where(function (Builder $query): void {
                $query->where(function (Builder $query): void {
                    $query->whereDoesntHave('addressDelivery')
                        ->orWhereHas('addressDelivery', function (Builder $query): void {
                            $query->whereNull('latitude')
                                ->orWhereNull('longitude');
                        });
                })
                    ->where(function (Builder $query): void {
                        $query->whereNull('address_delivery->latitude')
                            ->orWhereNull('address_delivery->longitude');
                    });
            })
            ->count();
    }

    #[Renderless]
    public function loadData(): void
    {
        parent::loadData();

        if ($this->showMap) {
            $this->updatedShowMap();
        }
    }

    #[Renderless]
    public function loadMap(): array
    {
        return $this->buildSearch()
            ->where(function (Builder $query): void {
                $query->whereHas('addressDelivery', function (Builder $query): void {
                    $query->whereNotNull('latitude')
                        ->whereNotNull('longitude');
                })
                    ->orWhere(function (Builder $query): void {
                        $query->whereNotNull('address_delivery->latitude')
                            ->whereNotNull('address_delivery->longitude');
                    });
            })
            ->when($this->mapLimit, fn (Builder $query): Builder => $query->limit($this->mapLimit))
            ->with([
                'addressDelivery:id,company,firstname,lastname,latitude,longitude,zip,city,street',
                'contact.mainAddress',
            ])
            ->get()
            ->toMap()
            ->toArray();
    }

    #[Renderless]
    public function updatedShowMap(): void
    {
        $this->dispatch('load-map');
    }

    protected function getBladeParameters(OffersPrinting $item): array|SerializableClosure|null
    {
        return new SerializableClosure(
            fn () => [
                'order' => resolve_static(Order::class, 'query')
                    ->whereKey($item->getKey())
                    ->first(),
            ]
        );
    }

    protected function getDefaultTemplateId(OffersPrinting $item): ?int
    {
        return $item->orderType?->email_template_id;
    }

    protected function getPrintLayouts(): array
    {
        return resolve_static(Order::class, 'query')
            ->whereKey($this->getSelectedValues())
            ->with('orderType')
            ->get(['id', 'order_type_id'])
            ->printLayouts();
    }

    protected function getPreferredLanguageId(OffersPrinting $item): ?int
    {
        return $item->language_id;
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        $printViews = $item->getPrintViews();

        $invoiceDocs = array_filter(
            $documents,
            function (string $doc) use ($printViews) {
                try {
                    return resolve_static(data_get($printViews, $doc), 'isInvoice');
                } catch (Throwable) {
                    return false;
                }
            }
        );

        $address = $invoiceDocs
            ? $item->resolveMailableInvoiceAddress()
            : $item->contact?->mainAddress;

        $to = $address?->mail_addresses ?? [];

        if (array_diff($documents, $invoiceDocs)) {
            $to[] = $item->contact?->mainAddress?->email_primary;
        }

        return array_values(array_unique(array_filter($to)));
    }

    protected function supportsDocumentPreview(): bool
    {
        return true;
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'priceLists' => resolve_static(PriceList::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'paymentTypes' => resolve_static(PaymentType::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'languages' => resolve_static(Language::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'tenants' => resolve_static(Tenant::class, 'query')
                    ->where('is_active', true)
                    ->get(['id', 'name'])
                    ->toArray(),
                'orderTypes' => resolve_static(OrderType::class, 'query')
                    ->where('is_hidden', false)
                    ->where('is_active', true)
                    ->get(['id', 'name'])
                    ->toArray(),
            ]
        );
    }
}
