<?php

namespace FluxErp\Actions\ResourceBooking;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ResourceBooking;
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
        $booking = app(ResourceBooking::class, ['attributes' => $this->getData()]);
        $booking->save();

        return $booking->refresh();
    }
}
