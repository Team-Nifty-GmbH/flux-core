<?php

namespace FluxErp\Rulesets\Loan;

use FluxErp\Models\Loan;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLoanRuleset extends FluxRuleset
{
    protected static ?string $model = Loan::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Loan::class]),
            ],
        ];
    }
}
