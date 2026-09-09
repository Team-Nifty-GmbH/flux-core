<?php

namespace FluxErp\Rulesets\LedgerBooking;

use FluxErp\Models\LedgerBooking;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteLedgerBookingRuleset extends FluxRuleset
{
    protected static ?string $model = LedgerBooking::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => LedgerBooking::class]),
            ],
        ];
    }
}
