<?php

namespace FluxErp\Models;

use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ProductCrossSelling extends Model implements Sortable
{
    use HasPackageFactory, HasUuid, SortableTrait;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_cross_selling_product');
    }

    public function buildSortQuery(): Builder
    {
        return static::query()->where('product_id', $this->product_id);
    }
}
