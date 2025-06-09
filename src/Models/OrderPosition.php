<?php

namespace FluxErp\Models;

use FluxErp\Casts\BcFloat;
use FluxErp\Casts\Money;
use FluxErp\Casts\Percentage;
use FluxErp\Enums\CreditAccountPostingEnum;
use FluxErp\Traits\CascadeSoftDeletes;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasTags;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\EloquentSortable\Sortable;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class OrderPosition extends FluxModel implements InteractsWithDataTables, Sortable
{
    use CascadeSoftDeletes, Commentable, HasAdditionalColumns, HasClientAssignment, HasFrontendAttributes,
        HasPackageFactory, HasParentChildRelations, HasSerialNumberRange, HasTags, HasUserModification, HasUuid,
        LogsActivity, SortableTrait;

    public array $sortable = [
        'order_column_name' => 'sort_number',
        'sort_when_creating' => true,
    ];

    protected $appends = [
        'unit_price',
    ];

    protected array $cascadeDeletes = [
        'children',
        'commission',
    ];

    protected static function booted(): void
    {
        static::deleted(function (OrderPosition $orderPosition): void {
            $orderPosition->workTime()->update(['order_position_id' => null]);
            $orderPosition->creditNoteCommission()->update(['credit_note_order_position_id' => null]);
            $orderPosition->order->recalculateOrderPositionSlugPositions();
        });

        static::saved(function (OrderPosition $orderPosition): void {
            if ($orderPosition->isDirty('sort_number') || $orderPosition->isDirty('parent_id')) {
                if ($orderPosition->isDirty('parent_id')) {
                    DB::statement('SET @row_number = 0');

                    resolve_static(OrderPosition::class, 'query')
                        ->where('order_id', $orderPosition->order_id)
                        ->where('parent_id', $orderPosition->getOriginal('parent_id'))
                        ->orderBy('sort_number')
                        ->update([
                            'sort_number' => DB::raw('(@row_number:=@row_number+1)'),
                        ]);
                }

                $orderPosition->order->recalculateOrderPositionSlugPositions();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'amount' => BcFloat::class,
            'discount_percentage' => Percentage::class,
            'margin' => Money::class,
            'provision' => Money::class,
            'purchase_price' => Money::class,
            'total_base_gross_price' => Money::class,
            'total_base_net_price' => Money::class,
            'total_gross_price' => Money::class,
            'total_net_price' => Money::class,
            'vat_price' => Money::class,
            'unit_net_price' => Money::class,
            'unit_gross_price' => Money::class,
            'vat_rate_percentage' => Percentage::class,
            'customer_delivery_date' => 'date:Y-m-d',
            'possible_delivery_date' => 'date:Y-m-d',
            'product_prices' => 'array',
            'post_on_credit_account' => CreditAccountPostingEnum::class,
            'is_alternative' => 'boolean',
            'is_net' => 'boolean',
            'is_free_text' => 'boolean',
            'is_bundle_position' => 'boolean',
        ];
    }

    public function buildSortQuery(): Builder
    {
        return static::query()
            ->where('order_id', $this->order_id)
            ->where('parent_id', $this->parent_id);
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

    public function createdOrderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class, 'created_from_id');
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

    public function getAvatarUrl(): ?string
    {
        return $this->product?->getAvatarUrl();
    }

    public function getChildrenAttribute(): Collection
    {
        return $this->children()->get()->append('children');
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getTagsAttribute(): Collection
    {
        return $this->tags()->get();
    }

    public function getUrl(): ?string
    {
        return $this->order?->getUrl();
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

    protected function slugPosition(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => array_reduce(
                explode('.', $value ?? ''),
                fn ($carry, $item) => (is_null($carry) ? '' : $carry . '.') . ltrim($item, '0')
            ),
            set: fn ($value) => array_reduce(
                explode('.', $value ?? ''),
                fn ($carry, $item) => (is_null($carry) ? '' : $carry . '.') . Str::padLeft($item, 8, '0')
            ),
        );
    }

    protected function subTotalGross(): string|float|null
    {
        $result = DB::select('
            WITH RECURSIVE child_items AS (
                SELECT id, total_gross_price, is_alternative
                FROM order_positions
                WHERE id = ?

                UNION ALL

                SELECT op.id, op.total_gross_price, op.is_alternative
                FROM order_positions op
                INNER JOIN child_items ci ON op.parent_id = ci.id
                WHERE op.is_alternative = false
            )
            SELECT SUM(total_gross_price) as total
            FROM child_items
            WHERE id != ? AND is_alternative = false
        ', [$this->getKey(), $this->getKey()]);

        return (string) data_get($result, '0.total', 0);
    }

    protected function subTotalNet(): string|float|null
    {
        $result = DB::select('
            WITH RECURSIVE child_items AS (
                SELECT id, total_net_price, is_alternative
                FROM order_positions
                WHERE id = ?

                UNION ALL

                SELECT op.id, op.total_net_price, op.is_alternative
                FROM order_positions op
                INNER JOIN child_items ci ON op.parent_id = ci.id
                WHERE op.is_alternative = false
            )
            SELECT SUM(total_net_price) as total
            FROM child_items
            WHERE id != ? AND is_alternative = false
        ', [$this->getKey(), $this->getKey()]);

        return (string) data_get($result, '0.total', 0);
    }

    protected function totalGrossPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->is_free_text && ! $value
                ? $this->subTotalGross() ?: null
                : $value
        );
    }

    protected function totalNetPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->is_free_text && ! $value
                ? $this->subTotalNet() ?: null
                : $value
        );
    }

    protected function unitPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->is_net
                ? $this->unit_net_price
                : $this->unit_gross_price
        );
    }
}
