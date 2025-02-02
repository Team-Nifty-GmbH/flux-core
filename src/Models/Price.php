<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Casts\Percentage;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Price extends FluxModel
{
    use HasFrontendAttributes, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected $appends = [
        'base_price',
        'base_price_flat',
        'root_price',
        'root_price_flat',
        'is_net',
        'gross',
        'net',
        'applied_discounts',
        'discount_flat',
        'discount_percentage',
        'root_discount_flat',
        'root_discount_percentage',
        'is_inherited',
    ];

    protected $with = [
        'priceList',
        'product:id,vat_rate_id',
        'product.vatRate:id,rate_percentage',
    ];

    public array $appliedDiscounts = [];

    public ?string $discountFlat = null;

    public ?string $discountPercentage = null;

    public ?string $rootDiscountFlat = null;

    public ?string $rootDiscountPercentage = null;

    public bool $isInherited = false;

    public ?Price $basePrice = null;

    public ?Price $rootPrice = null;

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

    public function basePriceFlat(): Attribute
    {
        return Attribute::get(
            fn () => $this->basePrice?->price
        );
    }

    public function basePrice(): Attribute
    {
        return Attribute::get(
            fn () => $this->basePrice
        );
    }

    public function rootPrice(): Attribute
    {
        return Attribute::get(
            fn () => $this->rootPrice
        );
    }

    public function rootPriceFlat(): Attribute
    {
        return Attribute::get(
            fn () => $this->rootPrice?->price
        );
    }

    public function rootDiscountFlat(): Attribute
    {
        return Attribute::get(
            fn () => $this->rootDiscountFlat
        );
    }

    public function rootDiscountPercentage(): Attribute
    {
        return Attribute::get(
            fn () => $this->rootDiscountPercentage
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
            fn () => $this->appliedDiscounts
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
