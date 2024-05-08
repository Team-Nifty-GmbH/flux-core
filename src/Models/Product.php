<?php

namespace FluxErp\Models;

use FluxErp\Enums\TimeUnitEnum;
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
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
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

    protected string $detailRouteName = 'products.id';

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

    public function bundleProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_bundle_product',
            'product_id',
            'bundle_product_id'
        )->withPivot('count');
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

    public function purchasePrice(float|int $amount): float
    {
        // TODO: add calculation for purchase price
        return 0;
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
}
