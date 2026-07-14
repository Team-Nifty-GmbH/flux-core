<?php

namespace FluxErp\Models;

use FluxErp\Casts\Money;
use FluxErp\Casts\Percentage;
use FluxErp\Settings\ProductSettings;
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
    ];

    protected $with = [
        'priceList',
        'product:id,vat_rate_id',
        'product.vatRate:id,rate_percentage',
    ];

    protected static function booted(): void
    {
        static::saved(function (Price $price): void {
            // A variant taking ownership through a direct price write supersedes its
            // materialized inherited copy for the same price list.
            if (! $price->is_inherited) {
                resolve_static(static::class, 'query')
                    ->where('price_list_id', $price->price_list_id)
                    ->where('product_id', $price->product_id)
                    ->where('is_inherited', true)
                    ->whereKeyNot($price->getKey())
                    ->get(['id'])
                    ->each
                    ->delete();
            }

            $childIds = $price->inheritableChildIds();

            if (empty($childIds)) {
                return;
            }

            $childIds = collect($childIds);

            $rawPrice = $price->getAttributes()['price'];

            $owningChildIds = resolve_static(static::class, 'query')
                ->where('price_list_id', $price->price_list_id)
                ->whereIntegerInRaw('product_id', $childIds)
                ->where('is_inherited', false)
                ->pluck('product_id');

            $targetChildIds = $childIds->diff($owningChildIds)->values();

            if ($targetChildIds->isEmpty()) {
                return;
            }

            $existingInheritedChildIds = resolve_static(static::class, 'query')
                ->where('price_list_id', $price->price_list_id)
                ->whereIntegerInRaw('product_id', $targetChildIds)
                ->where('is_inherited', true)
                ->pluck('product_id');

            if ($existingInheritedChildIds->isNotEmpty()) {
                resolve_static(static::class, 'query')
                    ->where('price_list_id', $price->price_list_id)
                    ->whereIntegerInRaw('product_id', $existingInheritedChildIds)
                    ->where('is_inherited', true)
                    ->update(['price' => $rawPrice]);
            }

            $targetChildIds->diff($existingInheritedChildIds)
                ->each(fn (int $childId) => resolve_static(static::class, 'query')
                    ->create([
                        'price_list_id' => $price->price_list_id,
                        'product_id' => $childId,
                        'price' => $rawPrice,
                        'is_inherited' => true,
                    ])
                );
        });

        static::deleted(function (Price $price): void {
            $childIds = $price->inheritableChildIds();

            if (empty($childIds)) {
                return;
            }

            resolve_static(static::class, 'query')
                ->where('price_list_id', $price->price_list_id)
                ->whereIntegerInRaw('product_id', $childIds)
                ->where('is_inherited', true)
                ->get(['id'])
                ->each
                ->delete();
        });
    }

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

    /**
     * Resolve the child product ids that should receive inherited-price propagation
     * for this price, or an empty array if any guard fails.
     */
    public function inheritableChildIds(): array
    {
        if ($this->is_inherited || ! app(ProductSettings::class)->variant_inheritance_enabled) {
            return [];
        }

        $parent = resolve_static(Product::class, 'query')
            ->whereKey($this->product_id)
            ->whereNull('parent_id')
            ->whereHas('children')
            ->first(['id']);

        return $parent?->children()->pluck('id')->all() ?? [];
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
