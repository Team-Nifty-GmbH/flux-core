<?php

namespace FluxErp\Rulesets\OrderTransaction;

use FluxErp\Models\Pivots\OrderTransaction;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\Numeric;
use FluxErp\Rulesets\FluxRuleset;

class UpdateOrderTransactionRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => OrderTransaction::class]),
            ],
            'amount' => [
                'sometimes',
                'required',
                app(Numeric::class),
            ],
            'is_accepted' => 'boolean',
        ];
    }
}
