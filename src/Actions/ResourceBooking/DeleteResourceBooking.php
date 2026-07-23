<?php

namespace FluxErp\Actions\ResourceBooking;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ResourceBooking;
use FluxErp\Rulesets\ResourceBooking\DeleteResourceBookingRuleset;

class DeleteResourceBooking extends FluxAction
{
    public static function models(): array
    {
        return [ResourceBooking::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteResourceBookingRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(ResourceBooking::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
