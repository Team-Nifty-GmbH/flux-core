<?php

namespace FluxErp\Models;

use FluxErp\Enums\ConfigurationPriceModeEnum;
use FluxErp\Enums\ConfigurationSelectionTypeEnum;
use FluxErp\Models\Pivots\ProductProductConfigurationGroup;
use FluxErp\Traits\Model\Filterable;
use FluxErp\Traits\Model\HasAttributeTranslations;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use FluxErp\Traits\Model\HasUuid;
use FluxErp\Traits\Model\LogsActivity;
use FluxErp\Traits\Model\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductConfigurationGroup extends FluxModel
{
    use Filterable, HasAttributeTranslations, HasPackageFactory, HasUserModification, HasUuid, LogsActivity,
        SoftDeletes;

    protected function casts(): array
    {
        return [
            'selection_type_enum' => ConfigurationSelectionTypeEnum::class,
            'price_mode_enum' => ConfigurationPriceModeEnum::class,
            'min_selections' => 'integer',
            'max_selections' => 'integer',
        ];
    }

    public function productConfigurationGroupItems(): HasMany
    {
        return $this->hasMany(ProductConfigurationGroupItem::class)->orderBy('sort');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_product_configuration_group')
            ->using(ProductProductConfigurationGroup::class)
            ->withPivot(['pivot_id', 'is_required', 'sort']);
    }

    protected function translatableAttributes(): array
    {
        return [
            'name',
        ];
    }
}
