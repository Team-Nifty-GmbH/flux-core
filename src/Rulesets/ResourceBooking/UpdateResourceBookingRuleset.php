<?php

namespace FluxErp\Rulesets\ResourceBooking;

use FluxErp\Models\Order;
use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rules\ResourceAvailable;
use FluxErp\Rulesets\FluxRuleset;

class UpdateResourceBookingRuleset extends FluxRuleset
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
            'order_id' => [
                'sometimes',
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Order::class, 'subject' => ResourceBooking::class]),
            ],
            'resource_id' => [
                'sometimes',
                'required',
                'integer',
                app(ModelExists::class, ['model' => Resource::class, 'subject' => ResourceBooking::class])
                    ->where('is_active', true),
            ],
            'assignable_type' => [
                'sometimes',
                'nullable',
                'string',
                app(MorphClassExists::class),
            ],
            'assignable_id' => [
                'sometimes',
                'nullable',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'assignable_type']),
            ],
            'start' => ['required_with:end', 'date', app(ResourceAvailable::class)],
            'end' => ['required_with:start', 'date', 'after:start'],
            'description' => 'sometimes|nullable|string',
        ];
    }
}
