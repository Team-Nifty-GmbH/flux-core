<?php

namespace FluxErp\Actions\LedgerBooking;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rulesets\LedgerBooking\UpdateLedgerBookingRuleset;

class UpdateLedgerBooking extends FluxAction
{
    public static function models(): array
    {
        return [LedgerBooking::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateLedgerBookingRuleset::class;
    }

    public function performAction(): LedgerBooking
    {
        $ledgerBooking = resolve_static(LedgerBooking::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $ledgerBooking->fill($this->getData());
        $ledgerBooking->save();

        return $ledgerBooking->withoutRelations()->fresh();
    }
}
