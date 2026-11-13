<?php

namespace FluxErp\Actions\LedgerBooking;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FixedAssetDepreciation;
use FluxErp\Models\LedgerBooking;
use FluxErp\Rulesets\LedgerBooking\DeleteLedgerBookingRuleset;
use Illuminate\Validation\ValidationException;

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

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(LedgerBooking::class, 'query')
            ->whereKey($this->getData('id'))
            ->where('source_type', morph_alias(FixedAssetDepreciation::class))
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'source_type' => [__('Depreciation bookings are managed by their fixed asset.')],
            ]);
        }
    }
}
