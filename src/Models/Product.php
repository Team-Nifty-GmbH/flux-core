<?php

namespace FluxErp\Models;

use Exception;
use FluxErp\Contracts\HasMediaForeignKey;
use FluxErp\Enums\BundleTypeEnum;
use FluxErp\Enums\TimeUnitEnum;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Pivots\ClientProduct;
use FluxErp\Models\Pivots\ProductProductOption;
use FluxErp\Support\Collection\ProductOptionCollection;
use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasAttributeTranslations;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasParentChildRelations;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasTags;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\Lockable;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Product extends FluxModel implements HasMedia, HasMediaForeignKey, InteractsWithDataTables
{
    use Categorizable, Commentable, Filterable, HasAdditionalColumns, HasAttributeTranslations, HasClientAssignment,
        HasFrontendAttributes, HasPackageFactory, HasParentChildRelations, HasSerialNumberRange, HasTags,
        HasUserModification, HasUuid, InteractsWithMedia, Lockable, LogsActivity, Searchable, SoftDeletes;

    public static string $iconName = 'square-3-stack-3d';

    protected ?string $detailRouteName = 'products.id';

    public static function calculateVariantName(
        ProductOptionCollection|Arrayable|array $productOptions,
        string $parentName
    ): string {
        return $parentName . ' - '
            . implode(
                ' ',
                $productOptions instanceof ProductOptionCollection
                    ? $productOptions->pluck('name')->toArray()
                    : resolve_static(ProductOption::class, 'query')
                        ->whereIntegerInRaw('id', $productOptions)
                        ->pluck('name')
                        ->toArray()
            );
    }

    public static function mediaReplaced(int|string|null $oldMediaId, int|string|null $newMediaId): void
    {
        static::query()
            ->where('cover_media_id', $oldMediaId)
            ->update(['cover_media_id' => $newMediaId]);
    }

    public static function scoutIndexSettings(): array
    {
        return [
            'filterableAttributes' => [
                'is_active',
                'parent_id',
            ],
            'sortableAttributes' => ['*'],
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (! $product->product_number) {
                $product->getSerialNumber('product_number');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'bundle_type_enum' => BundleTypeEnum::class,
            'time_unit_enum' => TimeUnitEnum::class,
            'search_aliases' => 'array',
            'is_active' => 'boolean',
            'is_highlight' => 'boolean',
            'is_bundle' => 'boolean',
            'is_service' => 'boolean',
            'is_shipping_free' => 'boolean',
            'has_serial_numbers' => 'boolean',
            'is_nos' => 'boolean',
            'is_active_export_to_web_shop' => 'boolean',
        ];
    }

    public function bundleProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_bundle_product',
            'product_id',
            'bundle_product_id'
        )->withPivot('count');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_product')->using(ClientProduct::class);
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    /**
     * @throws Exception
     */
    public function getAvatarUrl(): ?string
    {
        return $this->coverMedia?->getUrl('thumb')
            ?? $this->getFirstMedia('images')?->getUrl('thumb')
            ?? static::icon()->getUrl();
    }

    public function getChildProductOptions(): Collection
    {
        return resolve_static(ProductOptionGroup::class, 'query')
            ->whereHas(
                'productOptions.products',
                function (Builder $query) {
                    return $query->whereIntegerInRaw('product_id', $this->children->pluck('id'));
                }
            )
            ->with([
                'productOptions' => fn ($query) => $query
                    ->whereHas('products', function (Builder $query) {
                        return $query->whereIntegerInRaw('product_id', $this->children->pluck('id'));
                    })
                    ->select([
                        'product_options.id',
                        'product_option_group_id',
                        'name',
                    ]),
            ])
            ->get();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getUrl(): ?string
    {
        return $this->detailRoute();
    }

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }

    public function price(): Attribute
    {
        return Attribute::get(function () {
            return resolve_static(PriceHelper::class, 'make', ['product' => $this])
                ->when(is_a(auth()->user(), Address::class), function (PriceHelper $priceHelper) {
                    return $priceHelper->setContact(auth()->user()->contact);
                })
                ->price();
        });
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function productCrossSellings(): HasMany
    {
        return $this->hasMany(ProductCrossSelling::class);
    }

    public function productOptions(): BelongsToMany
    {
        return $this->belongsToMany(ProductOption::class, 'product_product_option')
            ->using(ProductProductOption::class);
    }

    public function productProperties(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductProperty::class,
            'product_product_property',
            'product_id',
            'product_prop_id'
        );
    }

    public function purchasePrice(float|int|null $amount = 1): ?Price
    {
        return $amount
            ? PriceHelper::make($this)
                ->setPriceList(resolve_static(PriceList::class, 'query')
                    ->where('is_purchase', true)
                    ->first()
                )
                ->price()
            : null;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public');
    }

    public function scopeWebshop(Builder $query): void
    {
        $query->with('coverMedia')
            ->withCount('children')
            ->where(function (Builder $query): void {
                $query->where(fn (Builder $query) => $query->whereHas('stockPostings')
                    ->orWhere('is_nos', true)
                )
                    ->orWhereHas('children', function (Builder $query): void {
                        $query->where(function (Builder $query): void {
                            $query->where('is_nos', true)
                                ->orWhereHas('stockPostings');
                        })
                            ->where('is_active_export_to_web_shop', true)
                            ->where('is_active', true);
                    });
            })
            ->where('is_active_export_to_web_shop', true)
            ->where('is_active', true)
            ->select(array_map(
                fn (string $column) => $this->getTable() . '.' . $column,
                [
                    'id',
                    'cover_media_id',
                    'parent_id',
                    'vat_rate_id',
                    'product_number',
                    'name',
                    'description',
                    'is_highlight',
                ]
            ));
    }

    public function stockPostings(): HasMany
    {
        return $this->hasMany(StockPosting::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'product_supplier');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    protected function translatableAttributes(): array
    {
        return [
            'name',
            'description',
        ];
    }
}
