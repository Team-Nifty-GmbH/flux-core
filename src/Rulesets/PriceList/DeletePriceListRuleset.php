<?php

namespace FluxErp\Rulesets\PriceList;

use FluxErp\Models\PriceList;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePriceListRuleset extends FluxRuleset
{
    protected static ?string $model = PriceList::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PriceList::class]),
            ],
        ];
    }
}
