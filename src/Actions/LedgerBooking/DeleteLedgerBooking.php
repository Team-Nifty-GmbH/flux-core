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

        $sourceType = resolve_static(LedgerBooking::class, 'query')
            ->whereKey($this->getData('id'))
            ->value('source_type');

        if (resolve_static(FixedAssetDepreciation::class, 'isMorphType', [$sourceType])) {
            throw ValidationException::withMessages([
                'source_type' => [__('Depreciation bookings are managed by their fixed asset.')],
            ]);
        }
    }
}
