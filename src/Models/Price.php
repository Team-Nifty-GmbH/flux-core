<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use TeamNiftyGmbH\DataTable\Casts\Money;
use TeamNiftyGmbH\DataTable\Casts\Percentage;

class Price extends Model
{
    use HasFrontendAttributes, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $appends = [
        'base_price',
        'is_net',
        'gross',
        'net',
        'applied_discounts',
        'discount_flat',
        'discount_percentage',
        'is_inherited',
    ];

    protected $guarded = [
        'id',
    ];

    protected $with = [
        'priceList',
        'product:id,vat_rate_id',
        'product.vatRate:id,rate_percentage',
    ];

    public array $appliedDiscounts = [];

    public ?string $discountFlat = null;

    public ?string $discountPercentage = null;

    public bool $isInherited = false;

    public ?Price $basePrice = null;

    protected function casts(): array
    {
        return [
            'price' => Money::class,
            'gross' => Money::class,
            'net' => Money::class,
            'discount_flat' => Money::class,
            'discount_percentage' => Percentage::class,
            'is_inherited' => 'boolean',
        ];
    }

    public function basePrice(): Attribute
    {
        return Attribute::get(
            fn () => $this->basePrice
        );
    }

    public function isNet(): Attribute
    {
        return Attribute::get(
            fn () => $this->priceList?->is_net
        );
    }

    public function gross(): Attribute
    {
        return Attribute::get(
            fn () => $this->getGross(data_get($this->product, 'vatRate.rate_percentage') ?: 0)
        );
    }

    public function net(): Attribute
    {
        return Attribute::get(
            fn () => $this->getNet(data_get($this->product, 'vatRate.rate_percentage') ?: 0)
        );
    }

    public function isInherited(): Attribute
    {
        return Attribute::get(
            fn () => $this->isInherited
        );
    }

    public function discountPercentage(): Attribute
    {
        return Attribute::get(
            fn () => $this->discountPercentage
        );
    }

    public function discountFlat(): Attribute
    {
        return Attribute::get(
            fn () => $this->discountFlat
        );
    }

    public function appliedDiscounts(): Attribute
    {
        return Attribute::get(
            fn () => collect($this->appliedDiscounts)->toArray()
        );
    }

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getGross($vat): string
    {
        return $this->is_net ? net_to_gross($this->price, $vat) : $this->price;
    }

    public function getNet($vat): string
    {
        return $this->is_net ? $this->price : gross_to_net($this->price, $vat);
    }
}
