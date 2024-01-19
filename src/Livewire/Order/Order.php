<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\OrderPosition\FillOrderPositions;
use FluxErp\Actions\Printing;
use FluxErp\Htmlables\TabButton;
use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Forms\OrderPositionForm;
use FluxErp\Livewire\Forms\OrderReplicateForm;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Language;
use FluxErp\Models\Media;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Livewire\WithTabs;
use FluxErp\View\Printing\PrintableView;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\ComponentAttributeBag;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableRowAttributes;
use WireUi\Traits\Actions;

class Order extends OrderPositionList
{
    use Actions, WithTabs;

    protected string $view = 'flux::livewire.order.order';

    protected ?string $selectValue = 'index';

    public OrderForm $order;

    public OrderReplicateForm $replicateOrder;

    public OrderPositionForm $orderPosition;

    public ?int $orderPositionIndex = null;

    public array $availableStates = [];

    public array $printLayouts = [];

    public array $selectedPrintLayouts = [];

    public array $paymentStates = [];

    public array $deliveryStates = [];

    public array $states = [];

    public bool $isSelectable = true;

    public bool $isDirtyData = false;

    public array $enabledCols = [
        'slug_position',
        'name',
        'unit_net_price',
        'amount',
        'total_net_price',
    ];

    public ?bool $isSearchable = false;

    public bool $isFilterable = false;

    public array $selectedOrderPositions = [];

    #[Url]
    public string $tab = 'order.order-positions';

    public function mount(?string $id = null): void
    {
        parent::mount();

        $this->filters = [
            [
                'column' => 'order_id',
                'operator' => '=',
                'value' => $id,
            ],
        ];

        $this->fetchOrder($id);

        $this->getAvailableStates(['payment_state', 'delivery_state', 'state']);

        $this->isSelectable = ! $this->order->is_locked;
    }

    public function getSelectAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-show' => '! record.is_bundle_position && ! record.is_locked',
        ]);
    }

    public function getRowAttributes(): DataTableRowAttributes
    {
        return DataTableRowAttributes::make()
            ->bind(
                'class',
                "{
                    'bg-gray-200 dark:bg-secondary-700 font-bold': (record.is_free_text && record.depth === 0 && record.has_children),
                    'opacity-90': record.is_alternative,
                    'opacity-50 sortable-filter': record.is_bundle_position,
                    'font-semibold': record.is_free_text
                }"
            );
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('pencil')
                ->color('primary')
                ->attributes([
                    'wire:click' => <<<'JS'
                            editOrderPosition(index).then(() => $openModal('edit-order-position'));
                        JS,
                    'x-show' => '! record.is_bundle_position',
                    'x-cloak' => true,
                ])
                ->when(! $this->order->is_locked),
            DataTableButton::make()
                ->icon('eye')
                ->attributes([
                    'x-cloak' => 'true',
                    'x-show' => 'record.product_id',
                    'wire:click' => 'showProduct(record.product_id)',
                ]),
        ];
    }

    public function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->wireClick('deleteSelectedOrderPositions(); showSelectedActions = false;'),
        ];
    }

    public function getBuilder(Builder $builder): Builder
    {
        return $builder->whereNull('parent_id')
            ->reorder('sort_number');
    }

    public function getFormatters(): array
    {
        return array_merge(
            parent::getFormatters(),
            [
                'slug_position' => 'string',
                'alternative_tag' => ['state', [__('Alternative') => 'negative']],
            ]
        );
    }

    public function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'client_id',
                'order_id',
                'parent_id',
                'price_id',
                'price_list_id',
                'product_id',
                'vat_rate_id',
                'warehouse_id',
                'amount',
                'amount_bundle',
                'discount_percentage',
                'total_base_gross_price',
                'total_base_net_price',
                'total_gross_price',
                'vat_price',
                'unit_net_price',
                'unit_gross_price',
                'vat_rate_percentage',
                'description',
                'name',
                'product_number',
                'sort_number',
                'is_alternative',
                'is_net',
                'is_free_text',
                'is_bundle_position',
                'depth',
                'has_children',
                'unit_price',
                'alternative_tag',
                'indentation',
            ]
        );
    }

    public function getResultFromQuery(Builder $query): array
    {
        $tree = to_flat_tree($query->get()->toArray());
        $returnKeys = $this->getReturnKeys();

        foreach ($tree as &$item) {
            $item = Arr::only(Arr::dot($item), $returnKeys);
            $item['indentation'] = '';
            $item['unit_price'] = $item['is_net'] ? ($item['unit_net_price'] ?? 0) : ($item['unit_gross_price'] ?? 0);
            $item['alternative_tag'] = $item['is_alternative'] ? __('Alternative') : '';

            if ($item['depth'] > 0) {
                $indent = $item['depth'] * 20;
                $item['indentation'] = <<<HTML
                    <div class="text-right indent-icon" style="width:{$indent}px;">
                    </div>
                    HTML;
            }
        }

        return $tree;
    }

    public function getLeftAppends(): array
    {
        return [
            'name' => 'indentation',
        ];
    }

    public function getRightAppends(): array
    {
        return [
            'name' => 'alternative_tag',
        ];
    }

    public function getTopAppends(): array
    {
        return [
            'name' => 'product_number',
        ];
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'vatRates' => VatRate::query()
                    ->get(['id', 'name', 'rate_percentage'])
                    ->toArray(),
                'priceLists' => PriceList::query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'paymentTypes' => PaymentType::query()
                    ->where('client_id', $this->order->client_id)
                    ->get(['id', 'name'])
                    ->toArray(),
                'languages' => Language::query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'clients' => Client::query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'orderTypes' => OrderType::query()
                    ->where('is_hidden', false)
                    ->where('is_active', true)
                    ->get(['id', 'name'])
                    ->toArray(),
            ]
        );
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('order.order-positions')
                ->label(__('Order positions')),
            TabButton::make('order.attachments')
                ->label(__('Attachments'))
                ->isLivewireComponent()
                ->wireModel('order'),
            TabButton::make('order.texts')
                ->label(__('Texts'))
                ->isLivewireComponent()
                ->wireModel('order'),
            TabButton::make('order.accounting')
                ->label(__('Accounting'))
                ->isLivewireComponent()
                ->wireModel('order'),
            TabButton::make('order.comments')
                ->label(__('Comments'))
                ->isLivewireComponent()
                ->wireModel('order'),
            TabButton::make('order.related')
                ->label(__('Related processes'))
                ->isLivewireComponent()
                ->wireModel('order'),
            TabButton::make('order.activities')
                ->label(__('Activities'))
                ->isLivewireComponent()
                ->wireModel('order'),
        ];
    }

    public function loadData(): void
    {
        if (! $this->isDirtyData) {
            parent::loadData();
        }
    }

    public function updatedTab(): void
    {
        $this->forceRender();
    }

    public function updatedOrderAddressInvoiceId(): void
    {
        $this->order->address_invoice = Address::query()
            ->whereKey($this->order->address_invoice_id)
            ->with('contact')
            ->first()
            ->toArray();

        $this->order->payment_type_id = $this->order->address_invoice['contact']['payment_type_id'] ?? null;
        $this->order->price_list_id = $this->order->address_invoice['contact']['price_list_id'] ?? null;
        $this->order->language_id = $this->order->address_invoice['language_id'];
        $this->order->contact_id = $this->order->address_invoice['contact_id'];
        $this->order->client_id = $this->order->address_invoice['client_id'];
    }

    public function updatedOrderAddressDeliveryId(): void
    {
        $this->order->address_delivery = Address::query()
            ->whereKey($this->order->address_delivery_id)
            ->first()
            ->toArray();
    }

    #[Renderless]
    public function save(): bool
    {
        $this->order->address_delivery = $this->order->address_delivery ?: [];
        try {
            $action = UpdateOrder::make($this->order->toArray())->checkPermission()->validate();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $order = $action->execute();
        $this->notification()->success(__('Order saved successfully!'));

        if ($this->initialized) {
            try {
                FillOrderPositions::make([
                    'order_id' => $order->id,
                    'order_positions' => array_filter($this->data, fn ($item) => ! $item['is_bundle_position']),
                    'simulate' => false,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return false;
            }
        }

        return true;
    }

    #[Renderless]
    public function delete(): void
    {
        try {
            DeleteOrder::make($this->order->toArray())
                ->checkPermission()
                ->validate()
                ->execute();

            $this->redirect(route('orders.orders'), true);
        } catch (\Exception $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    public function replicate(): void
    {
        $this->replicateOrder->fill($this->order->toArray());
        $this->fetchContactData();

        $this->js(<<<'JS'
            $openModal('replicate-order');
        JS);
    }

    #[Renderless]
    public function saveReplicate(): void
    {
        try {
            $this->replicateOrder->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->redirectRoute('orders.id', ['id' => $this->replicateOrder->id], navigate: true);
    }

    #[Renderless]
    public function fetchContactData(bool $replicate = false): void
    {
        $orderVariable = ! $replicate ? 'order' : 'replicateOrder';

        $contact = Contact::query()
            ->whereKey($this->{$orderVariable}->contact_id)
            ->with('mainAddress:id,contact_id')
            ->first();

        $this->{$orderVariable}->client_id = $contact->client_id;
        $this->{$orderVariable}->agent_id = $contact->agent_id ?: $this->{$orderVariable}->agent_id;
        $this->{$orderVariable}->address_invoice_id = $contact->invoice_address_id ?? $contact->mainAddress->id;
        $this->{$orderVariable}->address_delivery_id = $contact->delivery_address_id ?? $contact->mainAddress->id;
        $this->{$orderVariable}->price_list_id = $contact->price_list_id;
        $this->{$orderVariable}->payment_type_id = $contact->payment_type_id;

        if (! $replicate) {
            $this->order->address_invoice = Address::query()
                ->whereKey($this->order->address_invoice_id)
                ->select(['id', 'company', 'firstname', 'lastname', 'zip', 'city', 'street'])
                ->first()
                ->toArray();
        }
    }

    #[Renderless]
    public function downloadPreview(string $view): ?StreamedResponse
    {
        try {
            $pdf = Printing::make([
                'model_type' => \FluxErp\Models\Order::class,
                'model_id' => $this->order->id,
                'view' => $view,
                'preview' => true,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return null;
        }

        return response()->streamDownload(
            fn () => print ($pdf->pdf->output()),
            Str::finish($pdf->getFileName(), '.pdf')
        );
    }

    #[Renderless]
    public function createDocuments(): null|MediaStream|Media
    {
        if (! $this->save()) {
            return null;
        }

        $order = \FluxErp\Models\Order::query()
            ->whereKey($this->order->id)
            ->with('addresses')
            ->first();

        $hash = md5(json_encode($order->toArray()) . json_encode($order->orderPositions->toArray()));

        $createDocuments = [];
        foreach ($this->selectedPrintLayouts as $type => $selectedPrintLayout) {
            $this->selectedPrintLayouts[$type] = array_filter($selectedPrintLayout);
            $createDocuments = array_unique(
                array_merge(
                    $createDocuments,
                    array_keys($this->selectedPrintLayouts[$type]))
            );
        }

        // create the documents
        $mediaIds = [];
        $downloadIds = [];
        $printIds = [];
        $mailAttachments = [];
        foreach ($createDocuments as $createDocument) {
            $media = $order->getMedia(
                $createDocument,
                fn (BaseMedia $media) => $media->getCustomProperty('hash') === $hash
            )
                ->last();

            if (! $media || ($this->selectedPrintLayouts['force'][$createDocument] ?? false)) {
                try {
                    /** @var PrintableView $file */
                    $file = Printing::make([
                        'model_type' => \FluxErp\Models\Order::class,
                        'model_id' => $this->order->id,
                        'view' => $createDocument,
                    ])->checkPermission()->validate()->execute();

                    $media = $file->attachToModel();
                    $media->setCustomProperty('hash', $hash)->save();
                } catch (ValidationException|UnauthorizedException $e) {
                    exception_to_notifications($e, $this);

                    continue;
                }
            }

            $mediaIds[$createDocument] = $media->id;

            if ($this->selectedPrintLayouts['download'][$createDocument] ?? false) {
                $downloadIds[] = $media->id;
            }

            if ($this->selectedPrintLayouts['print'][$createDocument] ?? false) {
                // TODO: add to print queue for spooler
                $printIds[] = $media->id;
            }

            if ($this->selectedPrintLayouts['email'][$createDocument] ?? false) {
                $mailAttachments[] = [
                    'name' => $media->file_name,
                    'id' => $media->id,
                ];
            }
        }

        $this->fetchOrder($this->order->id);

        if (($this->selectedPrintLayouts['email'] ?? false) && $mailAttachments) {
            $to = [];

            $to[] = in_array('invoice', $createDocuments) && $order->contact->invoiceAddress
                ? $order->contact->invoiceAddress->email
                : $order->contact->mainAddress->email;

            if (array_keys($this->selectedPrintLayouts['email']) !== ['invoice']
                && $order->contact->mainAddress->email
            ) {
                $to[] = $order->contact->mainAddress->email;
            }

            $this->dispatch(
                'create',
                [
                    'to' => array_unique($to),
                    'subject' => Blade::render(
                        html_entity_decode($this->order->order_type['mail_subject']),
                        ['order' => $order]
                    ) ?: $this->order->order_type['name'] . ' ' . $this->order->order_number,
                    'attachments' => $mailAttachments,
                    'html_body' => Blade::render(
                        html_entity_decode($this->order->order_type['mail_body']),
                        ['order' => $order]
                    ),
                ]
            )->to('edit-mail');
        }

        if ($downloadIds) {
            $files = Media::query()
                ->whereIntegerInRaw('id', $downloadIds)
                ->get();

            if ($files->count() === 1) {
                return $files->first();
            }

            return MediaStream::create($this->order->order_type['name'] . '_' . $this->order->order_number . '.zip')
                ->addMedia($files);
        }

        return null;
    }

    public function updatedOrderState(): void
    {
        $this->getAvailableStates('state');

        $this->skipRender();
    }

    #[Renderless]
    public function editOrderPosition(?int $index = null): void
    {
        if (! is_null($index)) {
            $this->orderPositionIndex = $index;
            $this->orderPosition->fill($this->data[$index]);
        }

        $this->orderPosition->order_id = $this->order->id;
        $this->orderPosition->client_id = $this->orderPosition->client_id ?: $this->order->client_id;
        $this->orderPosition->price_list_id = $this->orderPosition->price_list_id ?: $this->order->price_list_id;
        $this->orderPosition->contact_id = $this->orderPosition->contact_id ?: $this->order->contact_id;

        if (is_null($index) && $this->orderPosition->product_id) {
            $this->changedProductId();
        }
    }

    #[Renderless]
    public function addOrderPosition(): bool
    {
        $this->orderPosition->calculate();

        try {
            $this->orderPosition->validate();
        } catch (ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->orderPosition->alternative_tag = $this->orderPosition->is_alternative ? __('Alternative') : null;

        if (is_null($this->orderPositionIndex)) {
            if (! $this->orderPosition->slug_position ?? false) {
                $slugPositions = array_column($this->data, 'slug_position');
                $this->orderPosition->slug_position = (int) Str::before(array_pop($slugPositions), '.') + 1;
            }
            $this->data[] = $this->itemToArray($this->orderPosition);

            // if product has bundle products, add them to the order
            if ($this->orderPosition->product_id) {
                Product::addGlobalScope('bundleProducts', function (Builder $builder) {
                    $builder->with('bundleProducts');
                });
                $product = Product::query()
                    ->whereHas('bundleProducts')
                    ->whereKey($this->orderPosition->product_id)
                    ->first();
                if ($product) {
                    $this->addBundlePositions($product, $this->orderPosition->slug_position);
                }
            }

        } else {
            $this->data[$this->orderPositionIndex] = $this->itemToArray($this->orderPosition);
        }

        $this->order->total_net_price = bcadd(
            $this->order->total_net_price,
            data_get($this->orderPosition, 'total_net_price', 0)
        );
        $this->order->total_gross_price = bcadd(
            $this->order->total_gross_price,
            data_get($this->orderPosition, 'total_gross_price', 0)
        );

        $this->recalculateOrderTotals();
        $this->orderPosition->reset();

        return true;
    }

    #[Renderless]
    public function changedProductId(?Product $product = null): void
    {
        $this->orderPosition->fillFormProduct($product);
    }

    #[Renderless]
    public function resetOrderPosition(): void
    {
        $this->orderPositionIndex = null;
        $this->orderPosition->reset();
    }

    #[Renderless]
    public function quickAdd(): bool
    {
        $productId = $this->orderPosition->product_id;
        $this->editOrderPosition();
        $this->orderPosition->product_id = $productId;
        $this->changedProductId();

        return $this->addOrderPosition();
    }

    #[Renderless]
    public function deleteSelectedOrderPositions(): void
    {
        if (($wildcardIndex = array_search('*', $this->selected)) !== false) {
            unset($this->selected[$wildcardIndex]);
        }

        $slugPositions = [];
        foreach ($this->selected as $index) {
            $slugPositions[] = $this->data[$index]['slug_position'];
            unset($this->data[$index]);
        }

        // remove all children
        if ($slugPositions) {
            foreach ($this->data as $index => $item) {
                if (Str::startsWith($item['slug_position'] . '.', $slugPositions)) {
                    unset($this->data[$index]);
                }
            }
        }

        $this->data = array_values($this->data);
        $this->recalculateOrderTotals();

        $this->reset('selected');
    }

    #[Renderless]
    public function deleteOrderPosition(): void
    {
        $selected = $this->selected;
        $this->selected = [$this->orderPositionIndex];

        $this->deleteSelectedOrderPositions();

        $this->selected = $selected;
    }

    public function showProduct(Product $product): void
    {
        $this->js(<<<JS
            \$openDetailModal('{$product->getUrl()}');
        JS);
    }

    private function getAvailableStates(array|string $fieldNames): void
    {
        $fieldNames = (array) $fieldNames;
        $model = new \FluxErp\Models\Order();

        foreach ($fieldNames as $fieldName) {
            $model->{$fieldName} = $this->order->{$fieldName};
            $states = \FluxErp\Models\Order::getStatesFor($fieldName)
                ->map(function ($item) {
                    return [
                        'label' => __($item),
                        'name' => $item,
                    ];
                });

            $this->availableStates[$fieldName] = $states
                ->whereIn(
                    'name',
                    array_merge(
                        [$model->{$fieldName}],
                        $model->{$fieldName}->transitionableStates()
                    )
                )
                ->toArray();
        }
    }

    private function addBundlePositions(Product $product, string $slugPrefix): void
    {
        $padLength = strlen((string) $product->bundleProducts->count());
        $indent = (str_word_count($slugPrefix, 0, '.') + 1) * 20;

        foreach ($product->bundleProducts ?? [] as $index => $bundleProduct) {
            $this->editOrderPosition();
            $this->orderPosition->fillFormProduct($bundleProduct);
            $this->orderPosition->amount = bcmul($bundleProduct->pivot->count, $this->orderPosition->amount);
            $this->orderPosition->amount_bundle = $bundleProduct->pivot->count;
            $this->orderPosition->is_bundle_position = true;
            $this->orderPosition->slug_position = $slugPrefix . '.' . Str::padLeft($index + 1, $padLength, '0');
            $this->orderPosition->indentation = <<<HTML
                    <div class="text-right indent-icon" style="width:{$indent}px;">
                    </div>
                    HTML;
            try {
                $this->addOrderPosition();
            } catch (ValidationException $e) {
                exception_to_notifications($e, $this);
            }

            if ($bundleProduct->bundleProducts->count() > 0) {
                $this->addBundlePositions($bundleProduct, $this->orderPosition->slug_position);
            }

        }
    }

    private function recalculateOrderTotals(): void
    {
        $this->order->total_net_price = 0;
        $this->order->total_gross_price = 0;
        $this->order->total_vats = [];

        foreach ($this->data as $item) {
            $this->order->total_net_price = bcadd($this->order->total_net_price, $item['total_net_price'] ?? 0);
            $this->order->total_gross_price = bcadd(
                $this->order->total_gross_price,
                $item['total_gross_price'] ?? 0
            );
            $this->order->total_vats[$item['vat_rate_percentage']]['total_vat_price'] = bcadd(
                $this->order->total_vats[$item['vat_rate_percentage']]['total_vat_price'] ?? 0,
                $item['vat_price'] ?? 0
            );
            $this->order->total_vats[$item['vat_rate_percentage']]['vat_rate_percentage'] = $item['vat_rate_percentage'];
        }

        $this->isDirtyData = true;
    }

    protected function fetchOrder(int $id): void
    {
        $order = \FluxErp\Models\Order::query()
            ->whereKey($id)
            ->with([
                'priceList:id,name,is_net',
                'addresses',
                'client:id,name',
                'contact.media',
                'contact.contactBankConnections:id,contact_id,iban',
                'currency:id,iso,name,symbol',
                'orderType:id,name,mail_subject,mail_body,print_layouts,order_type_enum',
            ])
            ->firstOrFail()
            ->append('avatar_url');

        $this->printLayouts = array_intersect(
            $order->orderType?->print_layouts ?: [],
            array_keys($order->resolvePrintViews())
        );

        $this->order->fill($order->toArray());
    }
}
