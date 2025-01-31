<?php

namespace FluxErp\Models;

use FluxErp\Casts\BcFloat;
use FluxErp\Casts\Money;
use FluxErp\Casts\Percentage;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasTags;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use FluxErp\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class OrderPosition extends FluxModel implements InteractsWithDataTables, Sortable
{
    use Commentable, HasAdditionalColumns, HasClientAssignment, HasFrontendAttributes, HasPackageFactory,
        HasSerialNumberRange, HasTags, HasUserModification, HasUuid, LogsActivity, SoftDeletes,
        SortableTrait {
            SortableTrait::setHighestOrderNumber as protected parentSetHighestOrderNumber;
        }

    protected $appends = [
        'unit_price',
    ];

    protected $guarded = [
        'id',
    ];

    public array $sortable = [
        'order_column_name' => 'sort_number',
        'sort_when_creating' => true,
    ];

    protected static function booted(): void
    {
        static::deleted(function (OrderPosition $orderPosition) {
            $orderPosition->workTime()->update(['order_position_id' => null]);
            $orderPosition->creditNoteCommission()->update(['credit_note_order_position_id' => null]);
            $orderPosition->commission()->delete();
        });
    }

    public function setHighestOrderNumber(): void
    {
        $this->parentSetHighestOrderNumber();

        $this->slug_position = ($this->parent ? $this->parent->slug_position . '.' : null)
            . $this->sort_number;
    }

    protected function casts(): array
    {
        return [
            'product_prices' => 'array',
            'total_net_price' => Money::class,
            'total_gross_price' => Money::class,
            'unit_net_price' => Money::class,
            'unit_gross_price' => Money::class,
            'vat_price' => Money::class,
            'margin' => Money::class,
            'provision' => Money::class,
            'purchase_price' => Money::class,
            'total_base_price' => Money::class,
            'total_base_gross_price' => Money::class,
            'total_base_net_price' => Money::class,
            'vat_rate_percentage' => Percentage::class,
            'customer_delivery_date' => 'date:Y-m-d',
            'possible_delivery_date' => 'date:Y-m-d',
            'discount_percentage' => Percentage::class,
            'amount' => BcFloat::class,
            'is_alternative' => 'boolean',
            'is_net' => 'boolean',
            'is_free_text' => 'boolean',
            'is_bundle_position' => 'boolean',
        ];
    }

    public function getChildrenAttribute(): Collection
    {
        return $this->children()->get()->append('children');
    }

    public function getTagsAttribute(): Collection
    {
        return $this->tags()->get();
    }

    protected function slugPosition(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => array_reduce(
                explode('.', $value),
                fn ($carry, $item) => (is_null($carry) ? '' : $carry . '.') . ltrim($item, '0')
            ),
            set: fn ($value) => array_reduce(
                explode('.', $value),
                fn ($carry, $item) => (is_null($carry) ? '' : $carry . '.') . Str::padLeft($item, 8, '0')
            ),
        );
    }

    protected function totalNetPrice(): Attribute
    {
        return Attribute::get(
            fn ($value) => $this->is_free_text && ! $value
                ? $this->subTotalNet() ?: null
                : $value
        );
    }

    protected function totalGrossPrice(): Attribute
    {
        return Attribute::get(
            fn ($value) => $this->is_free_text && ! $value
                ? $this->subTotalGross() ?: null
                : $value
        );
    }

    protected function unitPrice(): Attribute
    {
        return Attribute::get(
            fn ($value) => $this->is_net
                ? $this->unit_net_price
                : $this->unit_gross_price
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrderPosition::class, 'parent_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function commission(): HasOne
    {
        return $this->hasOne(Commission::class);
    }

    public function createdFrom(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class, 'created_from_id');
    }

    public function creditNoteCommission(): HasOne
    {
        return $this->hasOne(Commission::class, 'credit_note_order_position_id');
    }

    public function currency(): HasOneThrough
    {
        return $this->hasOneThrough(
            Currency::class,
            Order::class,
            'id',
            'id',
            'order_id',
            'currency_id'
        );
    }

    public function descendants(): HasMany
    {
        return $this->hasMany(OrderPosition::class, 'origin_position_id');
    }

    public function discounts(): MorphMany
    {
        return $this->morphMany(Discount::class, 'model');
    }

    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class, 'origin_position_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class, 'parent_id');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reservedStock(): BelongsToMany
    {
        return $this->belongsToMany(StockPosting::class, 'order_position_stock_posting')
            ->withPivot('reserved_amount');
    }

    public function serialNumbers(): HasManyThrough
    {
        return $this->hasManyThrough(
            SerialNumber::class,
            StockPosting::class,
            'order_position_id',
            'id',
            'id',
            'serial_number_id'
        );
    }

    public function siblings(): HasMany
    {
        return $this->hasMany(
            OrderPosition::class,
            'origin_position_id',
            'origin_position_id'
        );
    }

    public function stockPostings(): HasMany
    {
        return $this->hasMany(StockPosting::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function workTime(): HasOne
    {
        return $this->hasOne(WorkTime::class);
    }

    private function subTotalNet(): string
    {
        return (string) (($this->relations['children'] ?? $this->children())
            ->where('is_alternative', false)
            ->sum('total_net_price'));
    }

    private function subTotalGross(): string
    {
        return (string) (($this->relations['children'] ?? $this->children())
            ->where('is_alternative', false)
            ->sum('total_gross_price'));
    }

    public function buildSortQuery(): Builder
    {
        return static::query()
            ->where('order_id', $this->order_id)
            ->where('parent_id', $this->parent_id);
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): ?string
    {
        return $this->order?->getUrl();
    }

    public function getAvatarUrl(): ?string
    {
        return $this->product?->getAvatarUrl();
    }
}
