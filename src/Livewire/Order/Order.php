<?php

namespace FluxErp\Livewire\Order;

use Exception;
use FluxErp\Actions\Discount\DeleteDiscount;
use FluxErp\Actions\Discount\UpdateDiscount;
use FluxErp\Actions\Order\DeleteOrder;
use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Actions\Order\ToggleLock;
use FluxErp\Actions\Order\UpdateLockedOrder;
use FluxErp\Actions\Order\UpdateOrder;
use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Actions\OrderTransaction\DeleteOrderTransaction;
use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Actions\Transaction\DeleteTransaction;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Enums\FrequenciesEnum;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Htmlables\TabButton;
use FluxErp\Invokable\ProcessSubscriptionOrder;
use FluxErp\Livewire\Forms\DiscountForm;
use FluxErp\Livewire\Forms\OrderForm;
use FluxErp\Livewire\Forms\OrderReplicateForm;
use FluxErp\Livewire\Forms\ScheduleForm;
use FluxErp\Models\Address;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\Discount;
use FluxErp\Models\Language;
use FluxErp\Models\Media;
use FluxErp\Models\Order as OrderModel;
use FluxErp\Models\OrderType;
use FluxErp\Models\PaymentType;
use FluxErp\Models\PriceList;
use FluxErp\Models\Schedule;
use FluxErp\Models\VatRate;
use FluxErp\Traits\Livewire\Actions;
use FluxErp\Traits\Livewire\CreatesDocuments;
use FluxErp\Traits\Livewire\WithTabs;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\SerializableClosure\SerializableClosure;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Url;
use Livewire\Component;
use Spatie\MediaLibrary\Support\MediaStream;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Order extends Component
{
    use Actions, CreatesDocuments, WithTabs;

    public array $availableStates = [];

    public array $deliveryStates = [];

    public DiscountForm $discount;

    public OrderForm $order;

    public array $paymentStates = [];

    public OrderReplicateForm $replicateOrder;

    public ScheduleForm $schedule;

    public array $states = [];

    #[Url]
    public string $tab = 'order.order-positions';

    #[Locked]
    public string $view = 'flux::livewire.order.order';

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
                'frequencies' => array_map(
                    fn ($item) => ['value' => $item, 'label' => __(Str::headline($item))],
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
                    ->get(['id', 'contact_id', 'iban'])
                    ?->toArray() ?? [],
                'vatRates' => resolve_static(VatRate::class, 'query')
                    ->where('is_tax_exemption', true)
                    ->get(['id', 'name'])
                    ->toArray(),
            ]
        );
    }

    public function createDocuments(): null|MediaStream|Media
    {
        $hadInvoiceNumber = resolve_static(OrderModel::class, 'query')
            ->whereKey($this->order->id)
            ->whereNotNull('invoice_number')
            ->exists();

        $createDocuments = $this->createDocumentFromItems(
            resolve_static(OrderModel::class, 'query')
                ->whereKey($this->order->id)
                ->first()
        );

        $this->fetchOrder($this->order->id);

        if (
            ! $hadInvoiceNumber
            && $this->order->invoice_number
            && $this->order->parent_id
            && (
                resolve_static(OrderType::class, 'query')
                    ->whereKey($this->order->order_type_id)
                    ->value('order_type_enum')
                    ?->multiplier() ?? 1
            ) < 1
            && resolve_static(CreateTransaction::class, 'canPerformAction', [false])
            && resolve_static(CreateOrderTransaction::class, 'canPerformAction', [false])
            && resolve_static(OrderModel::class, 'query')
                ->whereKey($this->order->parent_id)
                ->where('balance', '>', 0)
                ->exists()
        ) {
            $this->dialog()
                ->question(
                    __('Even Out Balance?'),
                    __('This will subtract the gross total of this order from the parents invoice balance')
                )
                ->confirm(__('Yes'), 'evenOutBalance')
                ->cancel(__('Cancel'))
                ->send();
        }

        return $createDocuments;
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

    #[Renderless]
    public function deleteDiscount(Discount $discount): void
    {
        try {
            DeleteDiscount::make(['id' => $discount->id])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->recalculateOrderTotals();
        $this->fetchOrder($this->order->id);
    }

    #[Renderless]
    public function editDiscount(Discount $discount): void
    {
        $this->discount->reset();
        $this->discount->fill($discount);

        $this->js(<<<'JS'
            $modalOpen('edit-discount');
        JS);
    }

    #[Renderless]
    public function evenOutBalance(): void
    {
        $parentOrder = resolve_static(OrderModel::class, 'query')
            ->whereKey($this->order->parent_id)
            ->first();
        $amount = min(
            bcabs($this->order->total_gross_price),
            $parentOrder->balance
        );
        $bankConnectionId = resolve_static(BankConnection::class, 'query')
            ->where('is_active', true)
            ->where('is_virtual', true)
            ->value('id');

        if (! $bankConnectionId) {
            $this->toast()
                ->error(__('No virtual bank connection found'))
                ->send();

            return;
        }

        $createdTransactions = [];
        $createdOrderTransactions = [];

        try {
            $transaction = CreateTransaction::make([
                'bank_connection_id' => $bankConnectionId,
                'currency_id' => $this->order->currency_id,
                'value_date' => now()->toDateString(),
                'booking_date' => now()->toDateString(),
                'amount' => $amount,
                'purpose' => __('Even out balance from :order', ['order' => $this->order->order_number]),
            ])
                ->checkPermission()
                ->validate()
                ->execute();
            $createdTransactions[] = $transaction;

            $transactionRefund = CreateTransaction::make([
                'bank_connection_id' => $bankConnectionId,
                'currency_id' => $this->order->currency_id,
                'value_date' => now()->toDateString(),
                'booking_date' => now()->toDateString(),
                'amount' => bcmul($amount, -1),
                'purpose' => __('Even out balance from :order', ['order' => $parentOrder->order_number]),
            ])
                ->checkPermission()
                ->validate()
                ->execute();
            $createdTransactions[] = $transactionRefund;

            $createdOrderTransactions[] = CreateOrderTransaction::make([
                'order_id' => $this->order->parent_id,
                'transaction_id' => $transaction->getKey(),
                'amount' => $transaction->amount,
                'is_accepted' => true,
            ])
                ->checkPermission()
                ->validate()
                ->execute();

            $createdOrderTransactions[] = CreateOrderTransaction::make([
                'order_id' => $this->order->id,
                'transaction_id' => $transactionRefund->getKey(),
                'amount' => $transactionRefund->amount,
                'is_accepted' => true,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            foreach ($createdTransactions as $createdTransaction) {
                try {
                    DeleteTransaction::make([
                        'id' => $createdTransaction->getKey(),
                    ])
                        ->validate()
                        ->execute();
                } catch (ValidationException|UnauthorizedException $cleanupException) {
                    exception_to_notifications($cleanupException, $this);
                }
            }

            foreach ($createdOrderTransactions as $createdOrderTransaction) {
                try {
                    DeleteOrderTransaction::make([
                        'pivot_id' => $createdOrderTransaction->getKey(),
                    ])
                        ->validate()
                        ->execute();
                } catch (ValidationException|UnauthorizedException $cleanupException) {
                    exception_to_notifications($cleanupException, $this);
                }
            }

            return;
        }

        $this->fetchOrder($this->order->id);
    }

    #[Renderless]
    public function fetchContactData(bool $replicate = false): void
    {
        $orderVariable = ! $replicate ? 'order' : 'replicateOrder';

        $contact = resolve_static(Contact::class, 'query')
            ->whereKey($this->{$orderVariable}->contact_id)
            ->with('mainAddress:id,contact_id')
            ->first();

        $this->{$orderVariable}->client_id = $contact?->client_id
            ?? resolve_static(Client::class, 'default')->getKey();
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

    public function getAdditionalModelActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create Retoure'))
                ->color('red')
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
                    'wire:click' => 'replicate(\'' . OrderTypeEnum::Retoure->value . '\')',
                ]),
            DataTableButton::make()
                ->text(__('Create Split-Order'))
                ->icon('shopping-bag')
                ->color('indigo')
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
                    'wire:click' => 'replicate(\'' . OrderTypeEnum::SplitOrder->value . '\')',
                ]),
        ];
    }

    public function getTabs(): array
    {
        return [
            TabButton::make('order.order-positions')
                ->text(__('Order positions'))
                ->isLivewireComponent()
                ->wireModel('order'),
            TabButton::make('order.attachments')
                ->text(__('Attachments'))
                ->isLivewireComponent()
                ->wireModel('order.id'),
            TabButton::make('order.texts')
                ->text(__('Texts'))
                ->isLivewireComponent()
                ->wireModel('order'),
            TabButton::make('order.accounting')
                ->text(__('Accounting'))
                ->isLivewireComponent()
                ->wireModel('order'),
            TabButton::make('order.comments')
                ->text(__('Comments'))
                ->isLivewireComponent()
                ->wireModel('order.id'),
            TabButton::make('order.related')
                ->text(__('Related processes'))
                ->isLivewireComponent()
                ->wireModel('order'),
            TabButton::make('order.activities')
                ->text(__('Activities'))
                ->isLivewireComponent()
                ->wireModel('order.id'),
        ];
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
            ->first([
                'id',
                'parent_id',
                'created_from_id',
                'contact_id',
                'currency_id',
                'order_type_id',
                'price_list_id',
            ]);

        $order->calculatePrices()->save();

        $this->order->fill($order);
    }

    #[Renderless]
    public function reOrderDiscount(Discount $discount, int $index): bool
    {
        try {
            UpdateDiscount::make([
                'id' => $discount->id,
                'order_column' => $index + 1,
            ])
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->recalculateOrderTotals();
        $this->fetchOrder($this->order->id);

        return true;
    }

    #[Renderless]
    public function replicate(?string $orderTypeEnum = null): void
    {
        if (
            resolve_static(OrderType::class, 'query')
                ->where('is_active', true)
                ->where('order_type_enum', $orderTypeEnum)
                ->exists()
            && in_array($orderTypeEnum, [OrderTypeEnum::Retoure->value, OrderTypeEnum::SplitOrder->value])) {
            $this->redirectRoute(
                'orders.create-child-order',
                [
                    'orderId' => $this->order->id,
                    'type' => $orderTypeEnum,
                ],
                navigate: true
            );

            return;
        }

        $this->replicateOrder->fill($this->order->toArray());
        $this->fetchContactData();

        $this->replicateOrder->order_positions = null;

        $this->js(<<<'JS'
            $modalOpen('replicate-order');
        JS);
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
        $this->notification()->success(__(':model saved', ['model' => __('Order')]))->send();

        return true;
    }

    #[Renderless]
    public function saveDiscount(): bool
    {
        $this->discount->model_type = morph_alias(OrderModel::class);
        $this->discount->model_id = $this->order->id;

        try {
            $this->discount->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->discount->reset();
        $this->recalculateOrderTotals();
        $this->fetchOrder($this->order->id);

        return true;
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

    public function updatedOrderAddressDeliveryId(): void
    {
        $this->order->address_delivery = resolve_static(Address::class, 'query')
            ->whereKey($this->order->address_delivery_id)
            ->first()
            ->toArray();
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

        $this->notification()->success(__(':model saved', ['model' => __('Order')]))->send();
    }

    protected function fetchOrder(int $id): void
    {
        $order = resolve_static(OrderModel::class, 'query')
            ->whereKey($id)
            ->with([
                'addresses',
                'client:id,name',
                'contact.media' => fn (MorphMany $query) => $query->where('collection_name', 'avatar'),
                'contact.contactBankConnections:id,contact_id,iban',
                'currency:id,iso,name,symbol',
                'discounts' => fn (MorphMany $query) => $query->ordered()
                    ->select([
                        'id',
                        'name',
                        'model_type',
                        'model_id',
                        'discount',
                        'discount_percentage',
                        'discount_flat',
                        'order_column',
                        'is_percentage',
                    ]),
                'orderType:id,name,mail_subject,mail_body,print_layouts,order_type_enum',
                'priceList:id,name,is_net',
                'users:id,name',
            ])
            ->firstOrFail();

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

    protected function getHtmlBody(OffersPrinting $item): string
    {
        return html_entity_decode($item->orderType->mail_body);
    }

    protected function getPrintLayouts(): array
    {
        return resolve_static(OrderModel::class, 'query')
            ->whereKey($this->order->id)
            ->with('orderType')
            ->first(['id', 'order_type_id'])
            ->resolvePrintViews();
    }

    protected function getSubject(OffersPrinting $item): string
    {
        return html_entity_decode(
            $item->orderType->mail_subject ?? '{{ $order->orderType->name }} {{ $order->order_number }}'
        );
    }

    protected function getTo(OffersPrinting $item, array $documents): array
    {
        // add invoice address email if an invoice is being sent
        $address = in_array('invoice', $documents) && $item->contact->invoiceAddress
            ? $item->contact->invoiceAddress
            : $item->contact->mainAddress;

        $to = array_merge(
            [$address->email_primary],
            $address
                ->contactOptions()
                ->where('type', 'email')
                ->pluck('value')
                ->toArray()
        );

        // add primary email address if more than just the invoice is added
        if (array_diff($documents, ['invoice'])) {
            $to[] = $item->contact->mainAddress->email_primary;
        }

        return array_values(array_unique(array_filter($to)));
    }

    protected function supportsDocumentPreview(): bool
    {
        return true;
    }
}
