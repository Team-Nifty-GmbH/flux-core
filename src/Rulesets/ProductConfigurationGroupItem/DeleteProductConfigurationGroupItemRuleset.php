<?php

namespace FluxErp\Rulesets\ProductConfigurationGroupItem;

use FluxErp\Models\ProductConfigurationGroupItem;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteProductConfigurationGroupItemRuleset extends FluxRuleset
{
    protected static ?string $model = ProductConfigurationGroupItem::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ProductConfigurationGroupItem::class]),
            ],
        ];
    }
}
