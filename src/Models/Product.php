<?php

namespace FluxErp\Models;

use FluxErp\Enums\TimeUnitEnum;
use FluxErp\Helpers\PriceHelper;
use FluxErp\Models\Pivots\ClientProduct;
use FluxErp\Models\Pivots\ProductProductOption;
use FluxErp\Traits\Categorizable;
use FluxErp\Traits\Commentable;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasClientAssignment;
use FluxErp\Traits\HasFrontendAttributes;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasSerialNumberRange;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\InteractsWithMedia;
use FluxErp\Traits\Lockable;
use FluxErp\Traits\Scout\Searchable;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\Tags\HasTags;
use TeamNiftyGmbH\DataTable\Contracts\InteractsWithDataTables;

class Product extends Model implements HasMedia, InteractsWithDataTables
{
    use Categorizable, Commentable, Filterable, HasAdditionalColumns, HasClientAssignment, HasFrontendAttributes,
        HasPackageFactory, HasSerialNumberRange, HasTags, HasUserModification, HasUuid, InteractsWithMedia, Lockable,
        Searchable, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public array $translatable = [
        'name',
        'description',
    ];

    protected ?string $detailRouteName = 'products.id';

    public static string $iconName = 'square-3-stack-3d';

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (! $product->product_number) {
                $product->getSerialNumber('product_number');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'time_unit_enum' => TimeUnitEnum::class,
            'is_active' => 'boolean',
            'is_highlight' => 'boolean',
            'is_bundle' => 'boolean',
            'is_service' => 'boolean',
            'is_shipping_free' => 'boolean',
            'is_required_product_serial_number' => 'boolean',
            'is_nos' => 'boolean',
            'is_active_export_to_web_shop' => 'boolean',
        ];
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

    public function children(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_product')->using(ClientProduct::class);
    }

    public function coverMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'cover_media_id');
    }

    public function orderPositions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }

    public function productCrossSellings(): HasMany
    {
        return $this->hasMany(ProductCrossSelling::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
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

    public function purchasePrice(float|int $amount = 1): ?Price
    {
        return PriceHelper::make($this)
            ->setPriceList(resolve_static(PriceList::class, 'query')
                ->where('is_purchase', true)
                ->first()
            )->price();
    }

    public function stockPostings(): HasMany
    {
        return $this->hasMany(StockPosting::class);
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'product_supplier');
    }

    public function vatRate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->useDisk('public');
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
        return $this->detailRoute();
    }

    /**
     * @throws \Exception
     */
    public function getAvatarUrl(): ?string
    {
        return $this->coverMedia?->getUrl('thumb')
            ?? $this->getFirstMedia('images')?->getUrl('thumb')
            ?? static::icon()->getUrl();
    }

    public function scopeWebshop(Builder $query): void
    {
        $query->with('coverMedia')
            ->withCount('children')
            ->where(function (Builder $query) {
                $query->where(fn (Builder $query) => $query->whereHas('stockPostings')
                    ->orWhere('is_nos', true)
                )
                    ->orWhereHas('children', function (Builder $query) {
                        $query->where(function (Builder $query) {
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
}
