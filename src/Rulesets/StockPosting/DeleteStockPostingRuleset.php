<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\StockPosting;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteStockPostingRuleset extends FluxRuleset
{
    protected static bool $addAdditionalColumnRules = false;

    protected static ?string $model = StockPosting::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => StockPosting::class]),
            ],
        ];
    }
}
