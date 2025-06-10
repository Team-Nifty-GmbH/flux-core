<?php

namespace FluxErp\Rulesets\OrderTransaction;

use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteOrderTransactionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderTransaction::class]),
            ],
        ];
    }
}
