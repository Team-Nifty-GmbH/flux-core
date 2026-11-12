<?php

namespace FluxErp\Models\Pivots;

use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Models\ProductConfigurationRule;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductConfigurationRuleItem extends FluxPivot
{
    protected $table = 'product_configuration_rule_items';

    public function productConfigurationGroupItem(): BelongsTo
    {
        return $this->belongsTo(ProductConfigurationGroupItem::class, 'product_configuration_group_item_id');
    }

    public function productConfigurationRule(): BelongsTo
    {
        return $this->belongsTo(ProductConfigurationRule::class, 'product_configuration_rule_id');
    }
}
