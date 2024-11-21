<?php

namespace FluxErp\Livewire\Order;

use Exception;
use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Actions\Order\ToggleLock;
use FluxErp\Actions\Order\UpdateLockedOrder;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Enums\FrequenciesEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Htmlables\TabButton;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Livewire\Forms\OrderForm;
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
use FluxErp\Models\Schedule;
use FluxErp\Traits\Livewire\CreatesDocuments;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Order extends Component
{
    use Actions, CreatesDocuments, WithTabs;

    #[Locked]
    public string $view = 'flux::livewire.order.order';

    public OrderForm $order;

    public OrderReplicateForm $replicateOrder;

    public ScheduleForm $schedule;

    public array $availableStates = [];

    public array $paymentStates = [];

    public array $deliveryStates = [];

    public array $states = [];

    public array $selectedOrderPositions = [];

    public array $replicateOrderTypes = [];

    #[Url]
    public string $tab = 'order.order-positions';

    public function mount(?string $id = null): void
    {
        $this->fetchOrder($id);

        $orderType = resolve_static(OrderType::class, 'query')
            ->whereKey($this->order->order_type_id)
            ->first();

        $this->view = 'flux::livewire.order.' . (($value = $orderType?->order_type_enum->value) ? $value : 'order');

        $this->getAvailableStates(['payment_state', 'delivery_state', 'state']);

        if (in_array($value, [OrderTypeEnum::PurchaseSubscription->value, OrderTypeEnum::Subscription->value])) {
            $this->fillSchedule();
        }
    }

    public function render(): View
    {
        return view(
            $this->view,
            [
                'additionalModelActions' => $this->getAdditionalModelActions(),
                'priceLists' => resolve_static(PriceList::class, 'query')
                    ->get(['id', 'name'])
                    ->toArray(),
                'paymentTypes' => resolve_static(PaymentType::class, 'query')
                    ->whereRelation('clients', 'id', $this->order->client_id)
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

    public function getTabs(): array
    {
        return [
            TabButton::make('order.order-positions')
                ->label(__('Order positions'))
                ->isLivewireComponent()
                ->wireModel('order'),
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

    public function updatedOrderIsConfirmed(): void
    {
        $this->skipRender();

        try {
            UpdateLockedOrder::make([
                'id' => $this->order->id,
                'is_confirmed' => $this->order->is_confirmed,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }

        $this->notification()->success(__('Order saved successfully!'));
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
    }

    #[Renderless]
    public function save(): bool
    {
        $this->order->address_delivery = $this->order->address_delivery ?: [];
        try {
            $action = (
                resolve_static(OrderModel::class, 'query')->whereKey($this->order->id)->value('is_locked')
                    ? UpdateLockedOrder::make($this->order->toActionData())
                    : UpdateOrder::make($this->order->toActionData())
            )
                ->checkPermission()
                ->validate();

            $this->getAvailableStates(['state', 'payment_state', 'delivery_state']);
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $action->execute();
        $this->notification()->success(__('Order saved successfully!'));

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
        } catch (Exception $e) {
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

        $this->{$orderVariable}->client_id = $contact?->client_id ?? Client::default()->id;
        $this->{$orderVariable}->agent_id = $contact?->agent_id ?? $this->{$orderVariable}->agent_id;
        $this->{$orderVariable}->address_invoice_id = $contact?->invoice_address_id ?? $contact?->mainAddress?->id;
        $this->{$orderVariable}->address_delivery_id = $contact?->delivery_address_id ?? $contact?->mainAddress?->id;
        $this->{$orderVariable}->price_list_id = $contact?->price_list_id;
        $this->{$orderVariable}->payment_type_id = $contact?->payment_type_id;

        if (! $replicate) {
            $this->order->address_invoice = resolve_static(Address::class, 'query')
                ->whereKey($this->order->address_invoice_id)
                ->select(['id', 'company', 'firstname', 'lastname', 'zip', 'city', 'street'])
                ->first()
                ?->toArray();
        }
    }

    #[Renderless]
    public function saveStates(): void
    {
        try {
            UpdateLockedOrder::make([
                'id' => $this->order->id,
                'state' => $this->order->state,
                'payment_state' => $this->order->payment_state,
                'delivery_state' => $this->order->delivery_state,
            ])
                ->checkPermission()
                ->validate()
                ->execute();

            $this->getAvailableStates(['state', 'payment_state', 'delivery_state']);
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);
        }
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
    public function recalculateOrderTotals(): void
    {
        $this->order->total_net_price = 0;
        $this->order->total_gross_price = 0;
        $this->order->total_vats = [];
        $this->order->total_base_net_price = 0;

        $order = resolve_static(OrderModel::class, 'query')
            ->whereKey($this->order->id)
            ->first('id');

        $order->calculatePrices()->save();

        $this->order->fill($order->toArray());
    }

    #[Renderless]
    public function recalculateReplicateOrderPositions(): void
    {
        $this->replicateOrder->order_positions = array_values($this->replicateOrder->order_positions);
    }

    public function fillSchedule(): void
    {
        $schedule = resolve_static(Schedule::class, 'query')
            ->whereRelation('orders', 'id', $this->order->id)
            ->where('class', ProcessSubscriptionOrder::class)
            ->first();

        if ($schedule) {
            $this->schedule->fill($schedule->toArray());
        } else {
            $defaultOrderType = resolve_static(OrderType::class, 'query')
                ->whereKey($this->order->order_type_id)
                ->first()
                ->order_type_enum === OrderTypeEnum::PurchaseSubscription ?
                OrderTypeEnum::Purchase->value : OrderTypeEnum::Order->value;

            $this->schedule->parameters['orderTypeId'] = resolve_static(OrderType::class, 'query')
                ->where('order_type_enum', $defaultOrderType)
                ->where('is_active', true)
                ->where('is_hidden', false)
                ->value('id');
        }
    }

    #[Renderless]
    public function saveSchedule(): bool
    {
        $this->schedule->orders = [$this->order->id];
        $this->schedule->name = ProcessSubscriptionOrder::name();
        $this->schedule->parameters = [
            'orderId' => $this->order->id,
            'orderTypeId' => $this->schedule->parameters['orderTypeId'] ?? null,
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
        $createDocuments = $this->createDocumentFromItems(
            resolve_static(OrderModel::class, 'query')
                ->whereKey($this->order->id)
                ->first()
        );

        $this->fetchOrder($this->order->id);

        return $createDocuments;
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
            array_keys($this->getPrintLayouts())
        );

        $invoice = $order->invoice();
        if ($invoice) {
            $this->order->invoice = [
                'url' => $invoice->getUrl(),
                'mime_type' => $invoice->mime_type,
            ];
        }
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
        return resolve_static(OrderModel::class, 'query')
            ->whereKey($this->order->id)
            ->with('orderType')
            ->first(['id', 'order_type_id'])
            ->resolvePrintViews();
    }

    protected function supportsDocumentPreview(): bool
    {
        return true;
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        $to = [];

        // add invoice address email if an invoice is being sent
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
