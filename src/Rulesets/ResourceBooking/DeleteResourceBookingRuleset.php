<?php

namespace FluxErp\Rulesets\ResourceBooking;

use FluxErp\Models\ResourceBooking;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class DeleteResourceBookingRuleset extends FluxRuleset
{
    protected static ?string $model = ResourceBooking::class;

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => ResourceBooking::class]),
            ],
        ];
    }
}
