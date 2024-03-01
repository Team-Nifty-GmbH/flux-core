<?php

namespace FluxErp\Rulesets\StockPosting;

use FluxErp\Models\StockPosting;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteStockPostingRuleset extends FluxRuleset
{
    protected static ?string $model = StockPosting::class;

    protected static bool $addAdditionalColumnRules = false;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(StockPosting::class),
            ],
        ];
    }
}
