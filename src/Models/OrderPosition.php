<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use FluxErp\Traits\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Spatie\Tags\HasTags;
use TeamNiftyGmbH\DataTable\Casts\BcFloat;
use TeamNiftyGmbH\DataTable\Casts\Money;
use TeamNiftyGmbH\DataTable\Casts\Percentage;

class OrderPosition extends Model
{
    use HasAdditionalColumns, HasPackageFactory, HasFrontendAttributes, HasSerialNumberRange, HasTags, HasUserModification,
        HasUuid, SoftDeletes;

    protected $hidden = [
        'uuid',
    ];

    protected $appends = [
        'unit_price',
    ];

    protected $casts = [
        'uuid' => 'string',
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
        'discount_percentage' => Percentage::class,
        'amount' => BcFloat::class,
        'is_alternative' => 'boolean',
        'is_net' => 'boolean',
        'is_free_text' => 'boolean',
        'is_bundle_position' => 'boolean',
        'is_positive_operator' => 'boolean',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    protected static function booted()
    {
        static::addGlobalScope('withChildren', function ($builder) {
            $builder->with('children');
        });
    }

    public function getChildrenAttribute(): Collection
    {
        return $this->children()->get()->append('children');
    }

    public function getTagsAttribute(): Collection
    {
        return $this->tags()->get();
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

    public function ancestors(): HasMany
    {
        return $this->hasMany(OrderPosition::class, 'origin_position_id');
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

    public function discounts(): HasMany
    {
        return $this->hasMany(Discount::class, 'order_position_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(OrderPosition::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(OrderPosition::class, 'parent_id');
    }

    public function serialNumbers(): HasMany
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
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
}
