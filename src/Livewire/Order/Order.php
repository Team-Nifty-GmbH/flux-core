<?php

namespace FluxErp\Livewire\Order;

use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Actions\Order\ToggleLock;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\OrderPosition\FillOrderPositions;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Enums\FrequenciesEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Htmlables\TabButton;
use FluxErp\Jobs\ProcessSubscriptionOrderJob;
use FluxErp\Livewire\DataTables\OrderPositionList;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Forms\OrderPositionForm;
use FluxErp\Livewire\Forms\OrderReplicateForm;
use FluxErp\Livewire\Forms\ScheduleForm;
use FluxErp\Models\Address;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Language;
use FluxErp\Models\Media;
use FluxErp\Models\Order as OrderModel;
use FluxErp\Models\OrderPosition;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Product;
use FluxErp\Models\Schedule;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Livewire\CreatesDocuments;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\ComponentAttributeBag;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableRowAttributes;
use WireUi\Traits\Actions;

class Order extends OrderPositionList
{
    use Actions, CreatesDocuments, WithTabs;

    protected string $view = 'flux::livewire.order.order';

    protected ?string $selectValue = 'index';

    public OrderForm $order;

    public OrderReplicateForm $replicateOrder;

    public OrderPositionForm $orderPosition;

    public ScheduleForm $schedule;

    public ?int $orderPositionIndex = null;

    public array $availableStates = [];

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

    public array $replicateOrderTypes = [];

    #[Url]
    public string $tab = 'order.order-positions';

    public function getListeners(): array
    {
        return array_merge(
            parent::getListeners(),
            [
                'order:add-products' => 'addProducts',
            ]
        );
    }

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

        $orderType = resolve_static(OrderType::class, 'query')
            ->whereKey($this->order->order_type_id)
            ->first();

        $this->view = 'flux::livewire.order.' . (($value = $orderType?->order_type_enum->value) ? $value : 'order');

        $this->getAvailableStates(['payment_state', 'delivery_state', 'state']);

        $this->isSelectable = ! $this->order->is_locked;

        if (in_array($value, [OrderTypeEnum::PurchaseSubscription->value, OrderTypeEnum::Subscription->value])) {
            $this->fillSchedule();
        }
    }

    public function getSelectAttributes(): ComponentAttributeBag
    {
        return new ComponentAttributeBag([
            'x-show' => '! record.is_bundle_position && ! record.is_locked',
        ]);
    }

    protected function getRowAttributes(): DataTableRowAttributes
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

    protected function getRowActions(): array
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

    public function getAdditionalModelActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Create Retoure'))
                ->color('negative')
                ->when(function () {
                    return resolve_static(ReplicateOrder::class, 'canPerformAction', [false])
                        && $this->order->invoice_date
                        && resolve_static(OrderType::class, 'query')
                            ->whereKey($this->order->order_type_id)
                            ->whereIn('order_type_enum', [
                                OrderTypeEnum::Order->value,
                                OrderTypeEnum::SplitOrder->value,
                            ])
                            ->exists()
                        && resolve_static(OrderType::class, 'query')
                            ->where('order_type_enum', OrderTypeEnum::Retoure->value)
                            ->where('is_active', true)
                            ->exists();
                })
                ->attributes([
                    'class' => 'w-full',
                    'x-on:click' => '$wire.replicate(\'' . OrderTypeEnum::Retoure->value . '\')',
                ]),
            DataTableButton::make()
                ->label(__('Create Split-Order'))
                ->icon('shopping-bag')
                ->color('primary')
                ->when(function () {
                    return resolve_static(ReplicateOrder::class, 'canPerformAction', [false])
                        && ! $this->order->invoice_date
                        && resolve_static(OrderType::class, 'query')
                            ->whereKey($this->order->order_type_id)
                            ->where('order_type_enum', OrderTypeEnum::Order->value)
                            ->exists()
                        && resolve_static(OrderType::class, 'query')
                            ->where('order_type_enum', OrderTypeEnum::SplitOrder->value)
                            ->where('is_active', true)
                            ->where('is_hidden', false)
                            ->exists();
                })
                ->attributes([
                    'class' => 'w-full',
                    'x-on:click' => '$wire.replicate(\'' . OrderTypeEnum::SplitOrder->value . '\')',
                ]),
        ];
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

    protected function getLeftAppends(): array
    {
        return [
            'name' => 'indentation',
        ];
    }

    protected function getRightAppends(): array
    {
        return [
            'name' => 'alternative_tag',
        ];
    }

    protected function getTopAppends(): array
    {
        return [
            'name' => 'product_number',
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'additionalModelActions' => $this->getAdditionalModelActions(),
                'vatRates' => resolve_static(VatRate::class, 'query')
                    ->get(['id', 'name', 'rate_percentage'])
                    ->toArray(),
                'priceLists' => resolve_static(PriceList::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'paymentTypes' => resolve_static(PaymentType::class, 'query')
                    ->where('client_id', $this->order->client_id)
                    ->get(['id', 'name'])
                    ->toArray(),
                'languages' => resolve_static(Language::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'clients' => resolve_static(Client::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'orderTypes' => resolve_static(OrderType::class, 'query')
                    ->where('is_hidden', false)
                    ->where('is_active', true)
                    ->get(['id', 'name'])
                    ->toArray(),
                'frequencies' => array_map(
                    fn ($item) => ['name' => $item, 'label' => __(Str::headline($item))],
                    array_intersect(
                        FrequenciesEnum::getBasicFrequencies(),
                        [
                            'daily',
                            'dailyAt',
                            'weekly',
                            'weeklyOn',
                            'monthly',
                            'monthlyOn',
                            'twiceMonthly',
                            'lastDayOfMonth',
                            'quarterly',
                            'quarterlyOn',
                            'yearly',
                            'yearlyOn',
                        ]
                    )
                ),
                'contactBankConnections' => resolve_static(ContactBankConnection::class, 'query')
                    ->where('contact_id', $this->order->contact_id)
                    ->select(['id', 'contact_id', 'iban'])
                    ->pluck('iban', 'id')
                    ?->toArray() ?? [],
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
                ->wireModel('order.id'),
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
        $this->order->address_invoice = resolve_static(Address::class, 'query')
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
        $this->order->address_delivery = resolve_static(Address::class, 'query')
            ->whereKey($this->order->address_delivery_id)
            ->first()
            ->toArray();
    }

    public function toggleLock(): void
    {
        try {
            ToggleLock::make(['id' => $this->order->id])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->fetchOrder($this->order->id);
        $this->forceRender();
    }

    #[Renderless]
    public function save(): bool
    {
        $this->order->address_delivery = $this->order->address_delivery ?: [];
        try {
            $action = UpdateOrder::make($this->order->toArray())->checkPermission()->validate();

            $this->getAvailableStates('state');
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

    public function replicate(?string $orderTypeEnum = null): void
    {
        $this->replicateOrder->fill($this->order->toArray());
        $this->fetchContactData();

        $this->replicateOrderTypes = resolve_static(OrderType::class, 'query')
            ->where('order_type_enum', $orderTypeEnum)
            ->where('is_active', true)
            ->where('is_hidden', false)
            ->get(['id', 'name'])
            ->toArray();

        if ($this->replicateOrderTypes) {
            $this->replicateOrder->parent_id = $this->order->id;
            $this->replicateOrder->order_positions = [];
            if (count($this->replicateOrderTypes) === 1) {
                $this->replicateOrder->order_type_id = $this->replicateOrderTypes[0]['id'];
            }

            $this->forceRender();

            $this->js(<<<'JS'
                $openModal('create-child-order');
            JS);
        } else {
            $this->replicateOrder->order_positions = null;
            $this->skipRender();

            $this->js(<<<'JS'
                $openModal('replicate-order');
            JS);
        }
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

        $contact = resolve_static(Contact::class, 'query')
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
            $this->order->address_invoice = resolve_static(Address::class, 'query')
                ->whereKey($this->order->address_invoice_id)
                ->select(['id', 'company', 'firstname', 'lastname', 'zip', 'city', 'street'])
                ->first()
                ->toArray();
        }
    }

    #[Renderless]
    public function saveStates(): void
    {
        try {
            UpdateOrder::make([
                'id' => $this->order->id,
                'state' => $this->order->state,
                'payment_state' => $this->order->payment_state,
                'delivery_state' => $this->order->delivery_state,
            ])
                ->checkPermission()
                ->validate()
                ->execute();

            $this->getAvailableStates('state');
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }
    }

    #[Renderless]
    public function showProduct(Product $product): void
    {
        $this->js(<<<JS
            \$openDetailModal('{$product->getUrl()}');
        JS);
    }

    public function takeOrderPositions(array $positionIds): void
    {
        $orderPositions = resolve_static(OrderPosition::class, 'query')
            ->whereIntegerInRaw('order_positions.id', $positionIds)
            ->where('order_positions.order_id', $this->order->id)
            ->leftJoin('order_positions AS descendants', 'order_positions.id', '=', 'descendants.origin_position_id')
            ->selectRaw(
                'order_positions.id' .
                ', order_positions.amount' .
                ', order_positions.name' .
                ', order_positions.description' .
                ', SUM(COALESCE(descendants.amount, 0)) AS descendantAmount' .
                ', order_positions.amount - SUM(COALESCE(descendants.amount, 0)) AS totalAmount'
            )
            ->groupBy([
                'order_positions.id',
                'order_positions.amount',
                'order_positions.name',
                'order_positions.description',
            ])
            ->where('order_positions.is_bundle_position', false)
            ->havingRaw('order_positions.amount > descendantAmount')
            ->get();

        foreach ($orderPositions as $orderPosition) {
            $this->replicateOrder->order_positions[] = [
                'id' => $orderPosition->id,
                'amount' => $orderPosition->totalAmount,
                'name' => $orderPosition->name,
                'description' => $orderPosition->description,
            ];
        }
    }

    #[Renderless]
    public function deleteOrderPosition(): void
    {
        $selected = $this->selected;
        $this->selected = [$this->orderPositionIndex];

        $this->deleteSelectedOrderPositions();

        $this->selected = $selected;
    }

    #[Renderless]
    public function recalculateReplicateOrderPositions(): void
    {
        $this->replicateOrder->order_positions = array_values($this->replicateOrder->order_positions);
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
                app(Product::class)->addGlobalScope('bundleProducts', function (Builder $builder) {
                    $builder->with('bundleProducts');
                });
                $product = resolve_static(Product::class, 'query')
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
    public function addProducts(array|int $products): void
    {
        foreach (Arr::wrap($products) as $product) {
            if (is_array($product)) {
                $this->orderPosition->fill($product);
            } else {
                $this->orderPosition->product_id = $product;
            }

            $this->quickAdd();
        }
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

    public function fillSchedule(): void
    {
        $schedule = resolve_static(Schedule::class, 'query')
            ->where('class', ProcessSubscriptionOrderJob::class)
            ->whereJsonContains('parameters->order', $this->order->id)
            ->first();

        if ($schedule) {
            $this->schedule->fill($schedule->toArray());
        } else {
            $defaultOrderType = resolve_static(OrderType::class, 'query')
                ->whereKey($this->order->order_type_id)
                ->first()
                ->order_type_enum === OrderTypeEnum::PurchaseSubscription ?
                OrderTypeEnum::Purchase->value : OrderTypeEnum::Order->value;

            $this->schedule->parameters['orderType'] = resolve_static(OrderType::class, 'query')
                ->where('order_type_enum', $defaultOrderType)
                ->where('is_active', true)
                ->where('is_hidden', false)
                ->first()
                ?->id;
        }
    }

    #[Renderless]
    public function saveSchedule(): bool
    {
        $this->schedule->name = ProcessSubscriptionOrderJob::name();
        $this->schedule->parameters = [
            'order' => $this->order->id,
            'orderType' => $this->schedule->parameters['orderType'] ?? null,
        ];

        try {
            $this->schedule->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        return true;
    }

    public function createDocuments(): null|MediaStream|Media
    {
        return $this->createDocumentFromItems(
            resolve_static(OrderModel::class, 'query')
                ->whereKey($this->order->id)
                ->first()
        );
    }

    protected function getAvailableStates(array|string $fieldNames): void
    {
        $fieldNames = (array) $fieldNames;
        $model = app(OrderModel::class);

        foreach ($fieldNames as $fieldName) {
            $model->{$fieldName} = $this->order->{$fieldName};
            $states = app(OrderModel::class)->getStatesFor($fieldName)
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

    protected function fetchOrder(int $id): void
    {
        $order = resolve_static(OrderModel::class, 'query')
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

        $this->order->fill($order);
        $this->order->users = $order->users->pluck('id')->toArray();

        $this->printLayouts = array_map(
            fn (string $layout) => ['layout' => $layout, 'label' => __($layout)],
            $this->getPrintLayouts()
        );

        $invoice = $order->invoice();
        if ($invoice) {
            $this->order->invoice = [
                'url' => $invoice->getUrl(),
                'mime_type' => $invoice->mime_type,
            ];
        }
    }

    protected function addBundlePositions(Product $product, string $slugPrefix): void
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

    protected function recalculateOrderTotals(): void
    {
        $this->order->total_net_price = 0;
        $this->order->total_gross_price = 0;
        $this->order->total_vats = [];
        $this->order->total_base_net_price = 0;

        foreach ($this->data as $item) {
            $vatRatePercentage = bcadd($item['vat_rate_percentage'], 0);

            // calculate total net price
            $this->order->total_net_price = bcadd($this->order->total_net_price, $item['total_net_price'] ?? 0);

            // calculate total gross price
            $this->order->total_gross_price = bcadd(
                $this->order->total_gross_price,
                $item['total_gross_price'] ?? 0
            );

            // calculate total base net price
            $this->order->total_base_net_price = bcadd(
                $this->order->total_base_net_price,
                $item['total_base_net_price'] ?? 0
            );

            // calculate sum of vats
            $this->order->total_vats[$vatRatePercentage]['total_vat_price'] = bcadd(
                $this->order->total_vats[$vatRatePercentage]['total_vat_price'] ?? 0,
                $item['vat_price'] ?? 0
            );
            $this->order->total_vats[$vatRatePercentage]['vat_rate_percentage'] = $item['vat_rate_percentage'];
        }

        $this->isDirtyData = true;
    }

    protected function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            [
                'client_id',
                'ledger_account_id',
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
                'purchase_price',
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

    protected function getResultFromQuery(Builder $query): array
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

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->wireClick('deleteSelectedOrderPositions(); showSelectedActions = false;'),
        ];
    }

    protected function getBuilder(Builder $builder): Builder
    {
        return $builder->whereNull('parent_id')
            ->reorder('sort_number');
    }

    protected function getSubject(OffersPrinting $item): string
    {
        return html_entity_decode(
            $item->orderType->mail_subject ?? '{{ $order->orderType->name }} {{ $order->order_number }}'
        );
    }

    protected function getHtmlBody(OffersPrinting $item): string
    {
        return html_entity_decode($item->orderType->mail_body);
    }

    protected function getBladeParameters(OffersPrinting $item): array|SerializableClosure|null
    {
        return new SerializableClosure(
            fn () => [
                'order' => resolve_static(OrderModel::class, 'query')
                    ->whereKey($item->getKey())
                    ->first(),
            ]
        );
    }

    protected function getPrintLayouts(): array
    {
        return array_keys(
            resolve_static(OrderModel::class, 'query')
                ->whereKey($this->order->id)
                ->with('orderType')
                ->first(['id', 'order_type_id'])
                ->resolvePrintViews()
        );
    }

    protected function supportsDocumentPreview(): bool
    {
        return true;
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        $to = [];

        // add invoice address email if an invoice is being send
        $to[] = in_array('invoice', $documents) && $item->contact->invoiceAddress
            ? $item->contact->invoiceAddress->email_primary
            : $item->contact->mainAddress->email_primary;

        // add primary email address if more than just the invoice is added
        if (array_diff($documents, ['invoice'])) {
            $to[] = $item->contact->mainAddress->email_primary;
        }

        return array_values(array_unique(array_filter($to)));
    }
}
