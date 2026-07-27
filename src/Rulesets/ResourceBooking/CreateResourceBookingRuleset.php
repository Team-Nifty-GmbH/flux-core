<?php

namespace FluxErp\Rulesets\ResourceBooking;

use FluxErp\Models\Order;
use FluxErp\Models\Resource;
use FluxErp\Models\ResourceBooking;
use FluxErp\Rules\ModelExists;
use FluxErp\Rules\MorphClassExists;
use FluxErp\Rules\MorphExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateResourceBookingRuleset extends FluxRuleset
{
    protected static ?string $model = ResourceBooking::class;

    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string|uuid|unique:resource_bookings,uuid',
            'order_id' => [
                'nullable',
                'integer',
                app(ModelExists::class, ['model' => Order::class]),
            ],
            'resource_id' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Resource::class])
                    ->where('is_active', true),
            ],
            'assignable_type' => [
                'required_with:assignable_id',
                'nullable',
                'string',
                app(MorphClassExists::class),
            ],
            'assignable_id' => [
                'required_with:assignable_type',
                'nullable',
                'integer',
                app(MorphExists::class, ['modelAttribute' => 'assignable_type']),
            ],
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'description' => 'nullable|string',
        ];
    }
}
