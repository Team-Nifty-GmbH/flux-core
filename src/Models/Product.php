<?php

namespace FluxErp\Models;

use Exception;
use FluxErp\Contracts\HasMediaForeignKey;
use FluxErp\Enums\BundleTypeEnum;
use FluxErp\Enums\TimeUnitEnum;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Pivots\BundleProductProduct;
use FluxErp\Models\Pivots\ProductProductOption;
use FluxErp\Models\Pivots\ProductProductProperty;
use FluxErp\Models\Pivots\ProductSupplier;
use FluxErp\Models\Pivots\ProductTenant;
use FluxErp\Support\Collection\ProductOptionCollection;
use FluxErp\Traits\Model\Categorizable;
use FluxErp\Traits\Model\Commentable;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasAttributeTranslations;
use FluxErp\Traits\Model\HasFrontendAttributes;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasParentChildRelations;
use FluxErp\Traits\Model\HasSerialNumberRange;
use FluxErp\Traits\Model\HasTags;
use FluxErp\Traits\Model\HasTenantAssignment;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\InheritsFromParent;
use FluxErp\Traits\Model\InteractsWithMedia;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use FluxErp\Traits\Scout\Searchable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Product extends FluxModel implements HasMedia, HasMediaForeignKey, InteractsWithDataTables
{
    use Categorizable, Commentable, Filterable, HasAttributeTranslations, HasFrontendAttributes, HasPackageFactory,
        HasParentChildRelations, HasSerialNumberRange, HasTags, HasTenantAssignment, HasUserModification, HasUuid,
        InheritsFromParent, InteractsWithMedia, LogsActivity, SoftDeletes;
    use Searchable {
        Searchable::scoutIndexSettings as baseScoutIndexSettings;
    }

    public static string $iconName = 'square-3-stack-3d';

    protected ?string $detailRouteName = 'products.id';

    protected array $inheritableFields = [
        'cover_media_id',
        'purchase_unit_id',
        'reference_unit_id',
        'unit_id',
        'vat_rate_id',
        'name',
        'description',
        'weight_gram',
        'dimension_length_mm',
        'dimension_width_mm',
        'dimension_height_mm',
        'selling_unit',
        'basic_unit',
        'time_unit_enum',
        'customs_tariff_number',
        'min_delivery_time',
        'max_delivery_time',
        'restock_time',
        'seo_keywords',
        'posting_account',
        'has_serial_numbers',
        'is_active_export_to_web_shop',
        'is_highlight',
        'is_nos',
        'is_service',
        'is_shipping_free',
    ];

    protected array $inheritableRelations = [
        'categories',
        'prices',
        'productProperties',
        'suppliers',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (! $product->product_number) {
                $product->getSerialNumber('product_number');
            }
        });

        static::created(function (Product $product): void {
            if (! is_null($product->parent_id)) {
                static::query()
                    ->whereKey($product->parent_id)
                    ->update(['was_parent' => true]);
            }
        });
    }

    // Public static methods
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
                        ->whereKey($productOptions)
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

    public static function scoutIndexSettings(): ?array
    {
        return static::baseScoutIndexSettings() ?? [
            'filterableAttributes' => [
                'is_active',
                'parent_id',
            ],
        ];
    }

    protected function casts(): array
    {
        return [
            'bundle_type_enum' => BundleTypeEnum::class,
            'time_unit_enum' => TimeUnitEnum::class,
            'search_aliases' => 'array',
            'overridden_fields' => 'array',
            'has_serial_numbers' => 'boolean',
            'is_active' => 'boolean',
            'is_active_export_to_web_shop' => 'boolean',
            'is_bundle' => 'boolean',
            'is_highlight' => 'boolean',
            'is_nos' => 'boolean',
            'is_service' => 'boolean',
            'is_shipping_free' => 'boolean',
            'was_parent' => 'boolean',
        ];
    }

    // Relations
    public function bundleProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'bundle_product_product',
            'product_id',
            'bundle_product_id'
        )
            ->using(BundleProductProduct::class)
            ->withPivot('count');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }

    public function ownCategories(): MorphToMany
    {
        return $this->morphToMany(Category::class, 'categorizable', 'categorizable')
            ->using(Pivots\Categorizable::class);
    }

    public function ownPrices(): HasMany
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

    public function ownProductProperties(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductProperty::class,
            'product_product_property',
            'product_id',
            'product_property_id'
        )
            ->using(ProductProductProperty::class)
            ->withPivot('value');
    }

    public function stockPostings(): HasMany
    {
        return $this->hasMany(StockPosting::class);
    }

    public function ownSuppliers(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'product_supplier')
            ->using(ProductSupplier::class);
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'product_tenant')
            ->using(ProductTenant::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    // Public methods
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

    // Attributes
    public function getCategoriesAttribute(): Collection
    {
        return $this->resolveInheritedCollection(
            ownRelationMethod: 'ownCategories',
            resolvedRelation: 'categories',
            foreignKeyOnRelated: 'id'
        );
    }

    public function getPricesAttribute(): Collection
    {
        return $this->resolveInheritedCollection(
            ownRelationMethod: 'ownPrices',
            resolvedRelation: 'prices',
            foreignKeyOnRelated: 'price_list_id'
        );
    }

    public function getProductPropertiesAttribute(): Collection
    {
        return $this->resolveInheritedCollection(
            ownRelationMethod: 'ownProductProperties',
            resolvedRelation: 'productProperties',
            foreignKeyOnRelated: 'id'
        );
    }

    public function getSuppliersAttribute(): Collection
    {
        return $this->resolveInheritedCollection(
            ownRelationMethod: 'ownSuppliers',
            resolvedRelation: 'suppliers',
            foreignKeyOnRelated: 'id'
        );
    }

    protected function price(): Attribute
    {
        return Attribute::get(function () {
            return resolve_static(PriceHelper::class, 'make', ['product' => $this])
                ->when(is_a(auth()->user(), Address::class), function (PriceHelper $priceHelper) {
                    return $priceHelper->setContact(auth()->user()->contact);
                })
                ->price();
        });
    }

    // Scopes
    protected function scopeWebshop(Builder $query): void
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

    // Protected methods
    protected function translatableAttributes(): array
    {
        return [
            'name',
            'description',
        ];
    }
}
