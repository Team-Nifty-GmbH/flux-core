<?php

namespace FluxErp\Models;

use FluxErp\Enums\ConfigurationRuleTypeEnum;
use FluxErp\Models\Pivots\ProductConfigurationRuleItem;
use FluxErp\Traits\Model\HasPackageFactory;
use FluxErp\Traits\Model\HasUserModification;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductConfigurationRule extends FluxModel
{
    use HasPackageFactory, HasUserModification;

    protected function casts(): array
    {
        return [
            'rule_type_enum' => ConfigurationRuleTypeEnum::class,
        ];
    }

    public function productConfigurationGroupItem(): BelongsTo
    {
        return $this->belongsTo(ProductConfigurationGroupItem::class);
    }

    public function targetGroup(): BelongsTo
    {
        return $this->belongsTo(ProductConfigurationGroup::class, 'target_group_id');
    }

    public function targetItems(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductConfigurationGroupItem::class,
            'product_configuration_rule_items',
            'product_configuration_rule_id',
            'product_configuration_group_item_id'
        )
            ->using(ProductConfigurationRuleItem::class);
    }
}
