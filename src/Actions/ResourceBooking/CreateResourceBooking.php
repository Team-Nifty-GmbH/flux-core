<?php

namespace FluxErp\Actions\ResourceBooking;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ResourceBooking;
use FluxErp\Rules\ResourceAvailable;
use FluxErp\Rulesets\ResourceBooking\CreateResourceBookingRuleset;

class CreateResourceBooking extends FluxAction
{
    public static function models(): array
    {
        return [ResourceBooking::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateResourceBookingRuleset::class;
    }

    public function performAction(): ResourceBooking
    {
        $booking = app(ResourceBooking::class, ['attributes' => $this->data]);
        $booking->save();

        return $booking->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->addRules([
            'start' => [
                app(ResourceAvailable::class, [
                    'resourceId' => data_get($this->data, 'resource_id'),
                    'start' => data_get($this->data, 'start'),
                    'end' => data_get($this->data, 'end'),
                ]),
            ],
        ]);
    }
}
