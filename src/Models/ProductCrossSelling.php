<?php

namespace FluxErp\Models;

use FluxErp\Models\Pivots\ProductCrossSellingProduct;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SortableTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\Sortable;

class ProductCrossSelling extends FluxModel implements Sortable
{
    use HasPackageFactory, HasUuid, SortableTrait;

    protected $guarded = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_cross_selling_product')
            ->using(ProductCrossSellingProduct::class);
    }

    public function buildSortQuery(): Builder
    {
        return static::query()->where('product_id', $this->product_id);
    }
}
