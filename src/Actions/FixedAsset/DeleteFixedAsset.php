<?php

namespace FluxErp\Actions\FixedAsset;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FixedAsset;
use FluxErp\Rulesets\FixedAsset\DeleteFixedAssetRuleset;
use Illuminate\Validation\ValidationException;

class DeleteFixedAsset extends FluxAction
{
    public static function models(): array
    {
        return [FixedAsset::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteFixedAssetRuleset::class;
    }

    public function performAction(): ?bool
    {
        $fixedAsset = resolve_static(FixedAsset::class, 'query')
            ->whereKey($this->getData('id'))
            ->lockForUpdate()
            ->firstOrFail();

        $this->ensureNothingBooked($fixedAsset);

        return $fixedAsset->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->ensureNothingBooked(
            resolve_static(FixedAsset::class, 'query')
                ->whereKey($this->getData('id'))
                ->firstOrFail()
        );
    }

    protected function ensureNothingBooked(FixedAsset $fixedAsset): void
    {
        if ($fixedAsset->hasBookedDepreciations() || $fixedAsset->disposed_at) {
            throw ValidationException::withMessages([
                'id' => [__('A fixed asset with bookings or a disposal cannot be deleted.')],
            ]);
        }
    }
}
