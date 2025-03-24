<?php

namespace FluxErp\Rulesets\PriceList;

use FluxErp\Models\PriceList;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePriceListRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = PriceList::class;

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
