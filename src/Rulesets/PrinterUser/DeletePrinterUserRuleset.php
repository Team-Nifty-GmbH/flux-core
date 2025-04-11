<?php

namespace FluxErp\Rulesets\PrinterUser;

use FluxErp\Models\Pivots\PrinterUser;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeletePrinterUserRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'pivot_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => PrinterUser::class]),
            ],
        ];
    }
}
