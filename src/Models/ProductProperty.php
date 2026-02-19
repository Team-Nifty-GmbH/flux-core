<?php

namespace FluxErp\Models;

use FluxErp\Enums\PropertyTypeEnum;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasAttributeTranslations;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductProperty extends FluxModel
{
    use Filterable, HasAttributeTranslations, HasPackageFactory, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

    protected $hidden = [
        'pivot',
    ];

    protected function casts(): array
    {
        return [
            'property_type_enum' => PropertyTypeEnum::class,
        ];
    }

    public function productPropertyGroup(): BelongsTo
    {
        return $this->belongsTo(ProductPropertyGroup::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'product_product_property',
            'product_property_id',
            'product_id'
        );
    }

    protected function translatableAttributes(): array
    {
        return [
            'name',
        ];
    }
}
