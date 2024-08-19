<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductProperty extends Model
{
    use Filterable, HasPackageFactory, HasUserModification, HasUuid, SoftDeletes;

    protected $hidden = [
        'pivot',
    ];

    protected $guarded = [
        'id',
    ];

    public function productPropertyGroup(): BelongsTo
    {
        return $this->belongsTo(ProductPropertyGroup::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_product_property',
            'product_prop_id',
            'product_id'
        );
    }
}
