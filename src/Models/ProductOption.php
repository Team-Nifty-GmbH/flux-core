<?php

namespace FluxErp\Models;

use FluxErp\Support\Collection\ProductOptionCollection;
use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\LogsActivity;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductOption extends FluxModel
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, LogsActivity, SoftDeletes;

    protected $guarded = [
        'id',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_product_option');
    }

    public function productOptionGroup(): BelongsTo
    {
        return $this->belongsTo(ProductOptionGroup::class);
    }

    public function newCollection(array $models = []): Collection
    {
        return app(ProductOptionCollection::class, ['items' => $models]);
    }
}
