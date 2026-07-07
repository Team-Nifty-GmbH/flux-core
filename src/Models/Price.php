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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            $childIds = static::inheritableChildIdsForParentPrice($price);

            if (empty($childIds)) {
                return;
            }

            $childIds = collect($childIds);

            $rawPrice = $price->getAttributes()['price'];

            $owningChildIds = DB::table('prices')
                ->where('price_list_id', $price->price_list_id)
                ->where('is_inherited', false)
                ->whereNull('deleted_at')
                ->whereIn('product_id', $childIds)
                ->pluck('product_id');

            $targetChildIds = $childIds->diff($owningChildIds)->values();

            if ($targetChildIds->isEmpty()) {
                return;
            }

            $existingInheritedChildIds = DB::table('prices')
                ->where('price_list_id', $price->price_list_id)
                ->where('is_inherited', true)
                ->whereNull('deleted_at')
                ->whereIn('product_id', $targetChildIds)
                ->pluck('product_id');

            $now = now();

            if ($existingInheritedChildIds->isNotEmpty()) {
                DB::table('prices')
                    ->where('price_list_id', $price->price_list_id)
                    ->where('is_inherited', true)
                    ->whereNull('deleted_at')
                    ->whereIn('product_id', $existingInheritedChildIds)
                    ->update([
                        'price' => $rawPrice,
                        'updated_at' => $now,
                    ]);
            }

            $missingChildIds = $targetChildIds->diff($existingInheritedChildIds);

            if ($missingChildIds->isNotEmpty()) {
                DB::table('prices')->insert(
                    $missingChildIds->map(fn ($childId) => [
                        'uuid' => Str::uuid()->toString(),
                        'product_id' => $childId,
                        'price_list_id' => $price->price_list_id,
                        'price' => $rawPrice,
                        'is_inherited' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all()
                );
            }
        });

        static::deleted(function (Price $price): void {
            $childIds = static::inheritableChildIdsForParentPrice($price);

            if (empty($childIds)) {
                return;
            }

            DB::table('prices')
                ->where('price_list_id', $price->price_list_id)
                ->where('is_inherited', true)
                ->whereNull('deleted_at')
                ->whereIn('product_id', $childIds)
                ->update(['deleted_at' => now()]);
        });
    }

    /**
     * Resolve the child product ids that should receive inherited-price propagation
     * for the given parent price, or an empty array if any guard fails.
     */
    protected static function inheritableChildIdsForParentPrice(Price $price): array
    {
        if ($price->is_inherited || ! app(ProductSettings::class)->variant_inheritance_enabled) {
            return [];
        }

        $product = resolve_static(Product::class, 'query')
            ->whereKey($price->product_id)
            ->first();

        if (! $product || ! is_null($product->parent_id)) {
            return [];
        }

        $childIds = $product->children()->pluck('id');

        if ($childIds->isEmpty()) {
            return [];
        }

        return $childIds->all();
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
