<?php

namespace FluxErp\Models;

use Exception;
use FluxErp\Actions\OrderTransaction\CreateOrderTransaction;
use FluxErp\Actions\Transaction\CreateTransaction;
use FluxErp\Casts\Money;
use FluxErp\Casts\Percentage;
use FluxErp\Contracts\IsSubscribable;
use FluxErp\Contracts\OffersPrinting;
use FluxErp\Contracts\Targetable;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Events\Order\OrderApprovalRequestEvent;
use FluxErp\Models\Pivots\AddressAddressTypeOrder;
use FluxErp\Models\Pivots\OrderSchedule;
use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Rules\Numeric;
use FluxErp\States\Order\DeliveryState\DeliveryState;
use FluxErp\States\Order\OrderState;
use FluxErp\States\Order\PaymentState\Open;
use FluxErp\States\Order\PaymentState\Paid;
use FluxErp\States\Order\PaymentState\PartialPaid;
use FluxErp\States\Order\PaymentState\PaymentState;
use FluxErp\Support\Calculation\Rounding;
use FluxErp\Support\Collection\OrderCollection;
use FluxErp\Traits\CascadeSoftDeletes;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Communicatable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasRelatedModel;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Printable;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\Trackable;
use FluxErp\View\Printing\Order\DeliveryNote;
use FluxErp\View\Printing\Order\FinalInvoice;
use FluxErp\View\Printing\Order\Invoice;
use FluxErp\View\Printing\Order\Offer;
use FluxErp\View\Printing\Order\OrderConfirmation;
use FluxErp\View\Printing\Order\Refund;
use FluxErp\View\Printing\Order\Retoure;
use FluxErp\View\Printing\Order\SupplierOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\MediaLibrary\HasMedia;
use Spatie\ModelStates\HasStates;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Order extends FluxModel implements HasMedia, InteractsWithDataTables, IsSubscribable, OffersPrinting, Targetable
{
    use CascadeSoftDeletes, Commentable, Communicatable, Filterable, HasAdditionalColumns, HasClientAssignment,
        HasFrontendAttributes, HasPackageFactory, HasParentChildRelations, HasRelatedModel, HasSerialNumberRange,
        HasStates, HasUserModification, HasUuid, InteractsWithMedia, LogsActivity, Printable;
    use Searchable {
        Searchable::scoutIndexSettings as baseScoutIndexSettings;
    }
    use Trackable {
        Printable::resolvePrintViews as protected printableResolvePrintViews;
        HasSerialNumberRange::getSerialNumber as protected hasSerialNumberRangeGetSerialNumber;
    }

    public static string $iconName = 'shopping-bag';

    protected array $cascadeDeletes = [
        'orderPositions',
    ];

    protected ?string $detailRouteName = 'orders.id';

    protected $with = [
        'currency',
    ];

    public static function aggregateColumns(string $type): array
    {
        return match ($type) {
            'count' => ['id'],
            'sum', 'avg' => [
                'total_base_net_price',
                'total_base_gross_price',
                'total_base_discounted_net_price',
                'total_base_discounted_gross_price',
                'gross_profit',
                'total_purchase_price',
                'total_cost',
                'margin',
                'total_net_price',
                'total_gross_price',
                'balance',
            ],
            default => [],
        };
    }

    public static function aggregateTypes(): array
    {
        return [
            'avg',
            'count',
            'sum',
        ];
    }

    public static function getGenericChannelEvents(): array
    {
        return array_merge(
            parent::getGenericChannelEvents(),
            [
                'locked',
            ]
        );
    }

    public static function ownerColumns(): array
    {
        return [
            'approval_user_id',
            'agent_id',
            'responsible_user_id',
            'created_by',
            'updated_by',
        ];
    }

    public static function scoutIndexSettings(): ?array
    {
        return static::baseScoutIndexSettings() ?? [
            'filterableAttributes' => [
                'parent_id',
                'contact_id',
                'is_locked',
            ],
            'sortableAttributes' => ['*'],
        ];
    }

    public static function timeframeColumns(): array
    {
        return [
            'order_date',
            'invoice_date',
            'system_delivery_date',
            'system_delivery_date_end',
            'customer_delivery_date',
            'date_of_approval',
            'created_at',
            'updated_at',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Order $order): void {
            if ($order->isDirty('address_invoice_id')) {
                $addressInvoice = $order->addressInvoice()->first();
                $order->address_invoice = $addressInvoice;

                // Get additional attributes from address if not explicitly changed
                $order->language_id = $order->isDirty('language_id')
                    ? $order->language_id
                    : $addressInvoice->language_id;
                $order->contact_id = $order->isDirty('contact_id')
                    ? $order->contact_id
                    : $addressInvoice->contact_id;

                $contact = $order->contact()->first();
                $order->price_list_id = ! $contact->price_list_id || $order->isDirty('price_list_id')
                    ? $order->price_list_id
                    : $contact->price_list_id;
                $order->payment_type_id = ! $contact->payment_type_id || $order->isDirty('payment_type_id')
                    ? $order->payment_type_id
                    : $contact->payment_type_id;
                $order->client_id = ! $contact->client_id || $order->isDirty('client_id')
                    ? $order->client_id
                    : $contact->client_id;
            }

            if ($order->isDirty('address_delivery_id')
                && $order->address_delivery_id
                && ! $order->isDirty('address_delivery')
            ) {
                $order->address_delivery = $order->addressDelivery()->first();
            }

            // reset to original
            if ($order->wasChanged(['order_number', 'invoice_number'])) {
                $order->order_number = $order->getOriginal('order_number');
                $order->invoice_number = $order->getOriginal('invoice_number');
            }

            if (! $order->exists && ! $order->order_number) {
                $order->getSerialNumber('order_number');
            }

            if ($order->isDirty('invoice_date') || $order->isDirty('payment_target')) {
                $order->payment_target_date = ($order->invoice_date && ! is_null($order->payment_target))
                    ? $order->invoice_date->copy()->addDays($order->payment_target)
                    : null;
            }

            if ($order->isDirty('invoice_date') || $order->isDirty('payment_discount_target')) {
                $order->payment_discount_target_date = ($order->invoice_date && ! is_null($order->payment_discount_target))
                    ? $order->invoice_date->copy()->addDays($order->payment_discount_target)
                    : null;
            }

            if ($order->isDirty('invoice_number') && ! is_null($order->invoice_number)) {
                $orderPositions = $order->orderPositions()
                    ->whereNotNull('credit_account_id')
                    ->where('post_on_credit_account', '!=', 0)
                    ->get(['id', 'credit_account_id', 'credit_amount', 'post_on_credit_account']);

                DB::transaction(function () use ($order, $orderPositions): void {
                    foreach ($orderPositions as $orderPosition) {
                        $multiplier = match (true) {
                            $orderPosition->post_on_credit_account->value > 0 => 1,
                            $orderPosition->post_on_credit_account->value < 0 => -1,
                            default => 0,
                        };

                        if ($multiplier === 0) {
                            continue;
                        }

                        $transaction = CreateTransaction::make([
                            'contact_bank_connection_id' => $orderPosition->credit_account_id,
                            'currency_id' => $order->currency_id,
                            'value_date' => $order->order_date,
                            'booking_date' => $order->invoice_date,
                            'amount' => bcmul($orderPosition->credit_amount, $multiplier),
                            'purpose' => $orderPosition->getLabel(),
                        ])
                            ->validate()
                            ->execute();

                        if ($multiplier === -1) {
                            CreateOrderTransaction::make([
                                'transaction_id' => $transaction->id,
                                'order_id' => $order->id,
                                'amount' => $orderPosition->credit_amount,
                                'is_accepted' => true,
                            ])
                                ->validate()
                                ->execute();
                        }
                    }
                });

                $order->calculateBalance();

                if (is_null($order->payment_reminder_next_date) && ! is_null($order->invoice_date)) {
                    $order->payment_reminder_next_date = $order->invoice_date->addDays(
                        $order->payment_reminder_days_1
                    );
                }
            }

            if ($order->isDirty('payment_discount_percent')) {
                $order->calculateBalanceDueDiscount();
            }

            if ($order->isDirty('iban')
                && $order->iban
                && str_replace(' ', '', strtoupper($order->iban)) !== $order->contactBankConnection?->iban
            ) {
                $order->contact_bank_connection_id = null;
            }

            if (
                $order->contact_bank_connection_id
                && $order->isDirty('contact_bank_connection_id')
                && ! $order->isDirty('iban')
            ) {
                $order->iban = $order->contactBankConnection->iban;
                $order->account_holder = $order->contactBankConnection->account_holder;
                $order->bank_name = $order->contactBankConnection->bank_name;
                $order->bic = $order->contactBankConnection->bic;
            }

            if ($order->isDirty('iban') && $order->iban) {
                $order->iban = str_replace(' ', '', strtoupper($order->iban));
            }
        });

        static::saved(function (Order $order): void {
            if ($order->wasChanged('is_locked')) {
                $order->broadcastEvent('locked');
            }

            if (($order->wasChanged('approval_user_id') || $order->wasRecentlyCreated) && $order->approval_user_id) {
                $order->approvalUser?->subscribeNotificationChannel($order->broadcastChannel());

                event(OrderApprovalRequestEvent::make($order));
            }

            if (
                ($order->wasChanged('responsible_user_id') || $order->wasRecentlyCreated)
                && $order->responsible_user_id
            ) {
                $order->responsibleUser?->subscribeNotificationChannel($order->broadcastChannel());
            }
        });

        static::deleted(function (Order $order): void {
            $order->purchaseInvoice()->update(['order_id' => null]);
        });
    }

    protected function casts(): array
    {
        return [
            'address_invoice' => 'array',
            'address_delivery' => 'array',
            'state' => OrderState::class,
            'payment_state' => PaymentState::class,
            'delivery_state' => DeliveryState::class,
            'shipping_costs_net_price' => Money::class,
            'shipping_costs_gross_price' => Money::class,
            'shipping_costs_vat_price' => Money::class,
            'shipping_costs_vat_rate_percentage' => Percentage::class,
            'total_base_net_price' => Money::class,
            'total_base_gross_price' => Money::class,
            'total_base_discounted_net_price' => Money::class,
            'total_base_discounted_gross_price' => Money::class,
            'gross_profit' => Money::class,
            'total_purchase_price' => Money::class,
            'total_cost' => Money::class,
            'margin' => Money::class,
            'subtotal_net_price' => Money::class,
            'subtotal_gross_price' => Money::class,
            'subtotal_vats' => 'array',
            'total_net_price' => Money::class,
            'total_gross_price' => Money::class,
            'total_vats' => 'array',
            'total_discount_percentage' => Percentage::class,
            'total_discount_flat' => Money::class,
            'total_position_discount_percentage' => Percentage::class,
            'total_position_discount_flat' => Money::class,
            'balance' => Money::class,
            'payment_reminder_next_date' => 'date',
            'order_date' => 'date',
            'invoice_date' => 'date',
            'payment_target_date' => 'date',
            'payment_discount_target_date' => 'date',
            'system_delivery_date' => 'date',
            'system_delivery_date_end' => 'date',
            'customer_delivery_date' => 'date',
            'date_of_approval' => 'date',
            'is_locked' => 'boolean',
            'is_imported' => 'boolean',
            'is_confirmed' => 'boolean',
            'requires_approval' => 'boolean',
        ];
    }

    public function addressDelivery(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_delivery_id');
    }

    public function addresses(): BelongsToMany
    {
        return $this->belongsToMany(Address::class, 'address_address_type_order')
            ->using(AddressAddressTypeOrder::class)
            ->withPivot(['address_type_id', 'address']);
    }

    public function addressInvoice(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_invoice_id');
    }

    public function addressTypes(): BelongsToMany
    {
        return $this->belongstoMany(AddressType::class, 'address_address_type_order')
            ->withPivot('address_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function approvalUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approval_user_id');
    }

    public function calculateBalance(): static
    {
        $this->balance = bcround(
            bcsub(
                $this->total_gross_price,
                $this->totalPaid(),
                9
            ),
            2
        );

        return $this;
    }

    public function calculateBalanceDueDiscount(): static
    {
        if (! is_null($this->balance)
            && ! is_null($this->payment_discount_percent)
            && bccomp($this->payment_discount_percent, 0) > 0
        ) {
            $discountAmount = bcmul(bcabs($this->balance), $this->payment_discount_percent);
            $this->balance_due_discount = bcsub($this->balance, $discountAmount);
        } else {
            $this->balance_due_discount = null;
        }

        return $this;
    }

    public function calculateDiscounts(): static
    {
        $this->total_net_price = bcround(
            $this->discounts()
                ->ordered()
                ->get(['id', 'discount', 'is_percentage'])
                ->reduce(
                    function (string|float|int $previous, Discount $discount): string|float|int {
                        $new = $discount->is_percentage
                            ? max(0, discount($previous, $discount->discount))
                            : max(0, bcsub($previous, $discount->discount, 9));

                        $discount->update([
                            'discount_percentage' => diff_percentage($previous, $new),
                            'discount_flat' => bcsub($previous, $new, 9),
                        ]);

                        return $new;
                    },
                    $this->total_net_price ?? 0
                ),
            2
        );

        $this->total_discount_percentage = diff_percentage($this->total_base_net_price, $this->total_net_price);
        $this->total_discount_flat = bcsub($this->total_base_net_price, $this->total_net_price, 9);

        return $this;
    }

    public function calculateMargin(): static
    {
        $this->margin = Rounding::round(
            $this->orderPositions()
                ->where('is_alternative', false)
                ->sum('margin')
        );

        $variableCosts = 0;
        $variableCosts = bcadd($variableCosts, $this->commissions()->sum('commission'));
        $variableCosts = bcadd($variableCosts, $this->workTimes()->sum('total_cost'));
        $variableCosts = bcadd($variableCosts, $this->projects()->sum('total_cost'));
        $this->total_cost = $variableCosts;

        $this->gross_profit = Rounding::round(
            bcsub($this->margin, $this->total_cost, 9),
            2
        );

        return $this;
    }

    public function calculatePaymentState(): static
    {
        if (! $this->transactions()->exists()) {
            if ($this->payment_state->canTransitionTo(Open::class)) {
                $this->payment_state->transitionTo(Open::class);
            }
        } else {
            if (
                bccomp(
                    bcround(
                        $this->transactions()
                            ->withPivot('amount')
                            ->sum('order_transaction.amount'),
                        2
                    ),
                    bcround($this->total_gross_price, 2),
                    2
                ) === 0
            ) {
                if ($this->payment_state->canTransitionTo(Paid::class)) {
                    $this->payment_state->transitionTo(Paid::class);
                }
            } else {
                if ($this->payment_state->canTransitionTo(PartialPaid::class)) {
                    $this->payment_state->transitionTo(PartialPaid::class);
                }
            }
        }

        $this->calculateBalance();

        return $this;
    }

    public function calculatePrices(): static
    {
        return $this
            ->calculateTotalNetPrice()
            ->calculateDiscounts()
            ->calculateTotalGrossPrice()
            ->calculateMargin()
            ->calculateTotalVats();
    }

    public function calculateTotalGrossPrice(): static
    {
        $totalBaseGross = $this->orderPositions()
            ->where('is_alternative', false)
            ->sum('total_base_gross_price');

        $this->total_gross_price = discount(
            bcround(
                bcadd($totalBaseGross, $this->shipping_costs_gross_price ?: 0, 9),
                2
            ),
            $this->total_discount_percentage
        );

        $this->total_base_gross_price = bcround(
            bcadd($totalBaseGross, $this->shipping_costs_gross_price ?: 0, 9),
            2
        );
        $this->total_base_discounted_gross_price = $this->total_gross_price;

        return $this;
    }

    public function calculateTotalNetPrice(): static
    {
        $totalNet = $this->orderPositions()
            ->where('is_alternative', false)
            ->sum('total_net_price');
        $totalBaseNet = $this->orderPositions()
            ->where('is_alternative', false)
            ->sum('total_base_net_price');

        $this->total_net_price = bcround(
            bcadd($totalNet, $this->shipping_costs_net_price ?: 0, 9),
            2
        );
        $this->total_base_net_price = bcround(
            bcadd($totalBaseNet, $this->shipping_costs_net_price ?: 0, 9),
            2
        );
        $this->total_base_discounted_net_price = $this->total_net_price;

        $this->total_position_discount_percentage = diff_percentage(
            $this->total_base_net_price,
            $this->total_net_price
        );
        $this->total_position_discount_flat = bcsub(
            $this->total_base_net_price,
            $this->total_net_price,
            9
        );

        return $this;
    }

    public function calculateTotalVats(): static
    {
        $vatGroups = $this->orderPositions()
            ->where('is_alternative', false)
            ->whereNotNull('vat_rate_percentage')
            ->groupBy('vat_rate_percentage')
            ->selectRaw('sum(total_net_price) as total_net_price, vat_rate_percentage')
            ->get()
            ->keyBy('vat_rate_percentage');

        foreach ($this->discounts()->ordered()->get() as $discount) {
            if ($discount->is_percentage) {
                $vatGroups->transform(function (OrderPosition $item) use ($discount): OrderPosition {
                    $item->total_net_price = discount($item->total_net_price, $discount->discount);

                    return $item;
                });
            } else {
                $total = $vatGroups->reduce(function (string $carry, OrderPosition $item): string {
                    return bcadd($carry, $item->total_net_price, 9);
                }, '0');

                if (bccomp($total, '0', 9) > 0) {
                    $remainingTotal = max(0, bcsub($total, $discount->discount, 9));

                    $vatGroups->transform(function (OrderPosition $item) use ($total, $remainingTotal): OrderPosition {
                        $proportion = bcdiv($item->total_net_price, $total, 9);
                        $item->total_net_price = bcmul($remainingTotal, $proportion, 9);

                        return $item;
                    });
                }
            }
        }

        $this->total_vats = $vatGroups
            ->map(function (OrderPosition $item): array {
                return [
                    'vat_rate_percentage' => $item->vat_rate_percentage,
                    'total_vat_price' => bcround(bcmul($item->total_net_price, $item->vat_rate_percentage, 9), 2),
                    'total_net_price' => bcround($item->total_net_price, 2),
                ];
            })
            ->when($this->shipping_costs_vat_price, function (SupportCollection $vats): SupportCollection {
                return $vats->put(
                    $this->shipping_costs_vat_rate_percentage,
                    [
                        'vat_rate_percentage' => $this->shipping_costs_vat_rate_percentage,
                        'total_vat_price' => bcadd(
                            $this->shipping_costs_vat_price,
                            data_get($vats->get($this->shipping_costs_vat_rate_percentage), 'total_vat_price') ?? 0,
                            9
                        ),
                        'total_net_price' => bcadd(
                            $this->shipping_costs_net_price,
                            data_get($vats->get($this->shipping_costs_vat_rate_percentage), 'total_net_price') ?? 0,
                            9
                        ),
                    ]
                );
            })
            ->sortBy('vat_rate_percentage')
            ->values()
            ->toArray();

        return $this;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function contactBankConnection(): BelongsTo
    {
        return $this->belongsTo(ContactBankConnection::class);
    }

    public function costColumn(): ?string
    {
        return 'total_cost';
    }

    public function createdFrom(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'created_from_id');
    }

    public function createdOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'created_from_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function discounts(): MorphMany
    {
        return $this->morphMany(Discount::class, 'model');
    }

    /**
     * @throws Exception
     */
    public function getAvatarUrl(): ?string
    {
        return $this->contact?->getAvatarUrl() ?: static::icon()->getUrl();
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getEmailTemplateModelType(): ?string
    {
        return morph_alias(static::class);
    }

    public function getLabel(): ?string
    {
        return $this->orderType?->name . ' - ' . $this->order_number . ' - ' . data_get($this->address_invoice, 'name');
    }

    public function getPortalDetailRoute(): string
    {
        return route('portal.orders.id', ['id' => $this->id]);
    }

    public function getPrintViews(): array
    {
        // This has to be done this way, as this method is also called on order types settings with an empty order.
        if ($this->orderType?->order_type_enum->isPurchase()) {
            $printViews = [
                'supplier-order' => SupplierOrder::class,
            ];
        } else {
            $printViews = [
                'invoice' => Invoice::class,
                'final-invoice' => FinalInvoice::class,
                'offer' => Offer::class,
                'order-confirmation' => OrderConfirmation::class,
                'retoure' => Retoure::class,
                'refund' => Refund::class,
                'delivery-note' => DeliveryNote::class,
            ];
        }

        if ($this->orderType?->order_type_enum === OrderTypeEnum::Order) {
            $children = $this->children()
                ->pluck('invoice_number')
                ->toArray();

            if (
                $children
                && count($children) === count(array_filter($children))
            ) {
                // If all children have an invoice number, only show final invoice
                unset($printViews['invoice']);
            } elseif ($children) {
                // If the order has children, but not all have an invoice number, remove invoice and final invoice
                unset($printViews['invoice'], $printViews['final-invoice']);
            }
        } elseif ($this->orderType) {
            unset($printViews['final-invoice']);
        }

        return $printViews;
    }

    public function getSerialNumber(string|array $types, ?int $clientId = null): static
    {
        if (in_array('invoice_number', Arr::wrap($types))) {
            $rules = [
                'has_contact_delivery_lock' => 'declined',
            ];
            $data = [
                'has_contact_delivery_lock' => $this->contact->has_delivery_lock,
            ];
            $messages = [
                'has_contact_delivery_lock.declined' => __('The contact has a delivery lock'),
            ];

            if (! is_null($creditLine = $this->contact->credit_line)) {
                $rules['balance'] = app(Numeric::class, ['max' => $creditLine]);
                $data['balance'] = bcadd($this->contact->orders()->unpaid()->sum('balance'), $this->total_gross_price);
                $messages['balance'][get_class($rules['balance'])] = __('The credit line of the contact is exceeded');
            }

            Validator::make($data, $rules, $messages)->validate();
        }

        return $this->hasSerialNumberRangeGetSerialNumber($types, $clientId);
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    public function invoice(): ?\Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        return $this->getFirstMedia('invoice');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function newCollection(array $models = []): Collection
    {
        return app(OrderCollection::class, ['items' => $models]);
    }

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }

    public function orderTransactions(): HasMany
    {
        return $this->hasMany(OrderTransaction::class);
    }

    public function orderType(): BelongsTo
    {
        return $this->belongsTo(OrderType::class);
    }

    public function paymentReminders(): HasMany
    {
        return $this->hasMany(PaymentReminder::class);
    }

    public function paymentRuns(): BelongsToMany
    {
        return $this->belongsToMany(PaymentRun::class, 'order_payment_run');
    }

    public function paymentType(): BelongsTo
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function purchaseInvoice(): HasOne
    {
        return $this->hasOne(PurchaseInvoice::class);
    }

    public function recalculateOrderPositionSlugPositions(): static
    {
        DB::table('order_positions')
            ->where('order_id', $this->getKey())
            ->whereNull('parent_id')
            ->update([
                'slug_position' => DB::raw('LPAD(sort_number, 8, "0")'),
            ]);

        $query = "
            WITH RECURSIVE position_hierarchy AS (
                SELECT
                    id,
                    parent_id,
                    order_id,
                    slug_position,
                    sort_number,
                    0 AS level
                FROM
                    order_positions
                WHERE
                    order_id = ? AND parent_id IS NULL

                UNION ALL

                SELECT
                    c.id,
                    c.parent_id,
                    c.order_id,
                    CONCAT(p.slug_position, '.', LPAD(c.sort_number, 8, '0')) AS slug_position,
                    c.sort_number,
                    p.level + 1 AS level
                FROM
                    position_hierarchy p
                JOIN
                    order_positions c ON p.id = c.parent_id AND c.order_id = ?
            )

            UPDATE order_positions op,
                   position_hierarchy ph
            SET op.slug_position = ph.slug_position
            WHERE op.id = ph.id
              AND op.order_id = ?
              AND op.parent_id IS NOT NULL;
        ";

        DB::statement($query, [
            $this->getKey(),
            $this->getKey(),
            $this->getKey(),
        ]);

        return $this;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('invoice')
            ->acceptsMimeTypes(['application/pdf', 'image/jpeg', 'image/png', 'application/xml', 'text/xml'])
            ->singleFile()
            ->readOnly();

        $this->addMediaCollection('payment-reminders')
            ->acceptsMimeTypes(['application/pdf'])
            ->readOnly();

        $this->addMediaCollection('signature')
            ->acceptsMimeTypes(['image/jpeg', 'image/png'])
            ->useDisk('local')
            ->readOnly();
    }

    public function resolvePrintViews(): array
    {
        $printViews = $this->printableResolvePrintViews();

        return array_intersect_key($printViews, array_flip($this->orderType?->print_layouts ?: []));
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class)->using(OrderSchedule::class);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query
            ->whereNotNull('invoice_number')
            ->whereNotState('payment_state', Open::class);
    }

    public function scopePurchase(Builder $query): Builder
    {
        return $query->whereHas(
            'orderType',
            fn (Builder $query) => $query->whereIn(
                'order_type_enum',
                array_filter(OrderTypeEnum::cases(), fn (OrderTypeEnum $enum) => $enum->isPurchase())
            )
        );
    }

    public function scopeRevenue(Builder $query): Builder
    {
        return $query->whereHas(
            'orderType',
            fn (Builder $query) => $query->whereIn(
                'order_type_enum',
                array_filter(OrderTypeEnum::cases(), fn (OrderTypeEnum $enum) => ! $enum->isPurchase())
            )
        );
    }

    public function scopeUnpaid(Builder $query): Builder
    {
        return $query
            ->whereNotNull('invoice_number')
            ->whereNotState('payment_state', Paid::class)
            ->whereNot('balance', 0);
    }

    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Project::class);
    }

    public function totalPaid(): string|float|int
    {
        return $this->transactions()
            ->withPivot('amount')
            ->sum('order_transaction.amount');
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class)->using(OrderTransaction::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'order_user');
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    public function vatRates(): HasManyThrough
    {
        return $this->hasManyThrough(
            VatRate::class,
            OrderPosition::class,
            'order_id',
            'id',
            'id',
            'vat_rate_id'
        );
    }

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query->with(
            [
                'addresses',
            ]
        );
    }
}
