<?php

namespace FluxErp\Models;

use FluxErp\Traits\Filterable;
use FluxErp\Traits\HasPackageFactory;
use FluxErp\Traits\HasTranslations;
use FluxErp\Traits\HasUserModification;
use FluxErp\Traits\HasUuid;
use FluxErp\Traits\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionGroup extends Model
{
    use Filterable, HasPackageFactory, HasTranslations, HasUserModification, HasUuid, SoftDeletes;

    protected $hidden = [
        'uuid',
    ];

    protected $casts = [
        'uuid' => 'string',
    ];

    protected $guarded = [
        'id',
        'uuid',
    ];

    public array $translatable = [
        'name',
    ];

    public function productOptions(): HasMany
    {
        return $this->hasMany(ProductOption::class, 'product_option_group_id');
    }
}
