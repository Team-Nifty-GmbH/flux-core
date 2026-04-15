<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Casts\Percentage;
use FluxErp\Traits\Model\HasFrontendAttributes;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Price extends FluxModel
{
    use HasFrontendAttributes, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    public array $appliedDiscounts = [];

    public ?Price $basePrice = null;

    public ?string $discountFlat = null;

    public ?string $discountPercentage = null;

    public bool $isInherited = false;

    public ?string $rootDiscountFlat = null;

    public ?string $rootDiscountPercentage = null;

    public ?Price $rootPrice = null;

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

    // Relations
    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Public methods
    public function getGross($vat): ?string
    {
        if (is_null($this->price)) {
            return null;
        }

        return $this->is_net ? net_to_gross($this->price, $vat) : $this->price;
    }

    public function getNet($vat): ?string
    {
        if (is_null($this->price)) {
            return null;
        }

        return $this->is_net ? $this->price : gross_to_net($this->price, $vat);
    }

    // Attributes
    protected function appliedDiscounts(): Attribute
    {
        return Attribute::get(
            fn () => $this->appliedDiscounts
        );
    }

    protected function basePrice(): Attribute
    {
        return Attribute::get(
            fn () => $this->basePrice
        );
    }

    protected function basePriceFlat(): Attribute
    {
        return Attribute::get(
            fn () => $this->basePrice?->price
        );
    }

    protected function discountFlat(): Attribute
    {
        return Attribute::get(
            fn () => $this->discountFlat
        );
    }

    protected function discountPercentage(): Attribute
    {
        return Attribute::get(
            fn () => $this->discountPercentage
        );
    }

    protected function gross(): Attribute
    {
        return Attribute::get(
            fn () => $this->getGross(data_get($this->product, 'vatRate.rate_percentage') ?: 0)
        );
    }

    protected function isInherited(): Attribute
    {
        return Attribute::get(
            fn () => $this->isInherited
        );
    }

    protected function isNet(): Attribute
    {
        return Attribute::get(
            fn () => $this->priceList?->is_net
        );
    }

    protected function net(): Attribute
    {
        return Attribute::get(
            fn () => $this->getNet(data_get($this->product, 'vatRate.rate_percentage') ?: 0)
        );
    }

    public function rule(): BelongsTo
    {
        return $this->belongsTo(Rule::class);
    }

    protected function rootDiscountFlat(): Attribute
    {
        return Attribute::get(
            fn () => $this->rootDiscountFlat
        );
    }

    protected function rootDiscountPercentage(): Attribute
    {
        return Attribute::get(
            fn () => $this->rootDiscountPercentage
        );
    }

    protected function rootPrice(): Attribute
    {
        return Attribute::get(
            fn () => $this->rootPrice
        );
    }

    protected function rootPriceFlat(): Attribute
    {
        return Attribute::get(
            fn () => $this->rootPrice?->price
        );
    }
}
