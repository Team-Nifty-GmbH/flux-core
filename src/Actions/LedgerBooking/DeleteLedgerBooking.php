<?php

namespace FluxErp\Actions\LedgerBooking;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rulesets\LedgerBooking\DeleteLedgerBookingRuleset;

class DeleteLedgerBooking extends FluxAction
{
    public static function models(): array
    {
        return [LedgerBooking::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteLedgerBookingRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(LedgerBooking::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail()
            ->delete();
    }
}
