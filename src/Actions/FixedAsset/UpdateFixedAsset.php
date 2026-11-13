<?php

namespace FluxErp\Actions\FixedAsset;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FixedAsset;
use FluxErp\Rulesets\FixedAsset\UpdateFixedAssetRuleset;
use FluxErp\Traits\Action\ValidatesFixedAsset;
use Illuminate\Validation\ValidationException;

class UpdateFixedAsset extends FluxAction
{
    use ValidatesFixedAsset;

    public static function models(): array
    {
        return [FixedAsset::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateFixedAssetRuleset::class;
    }

    public function performAction(): FixedAsset
    {
        $fixedAsset = resolve_static(FixedAsset::class, 'query')
            ->whereKey($this->getData('id'))
            ->lockForUpdate()
            ->firstOrFail();
        $fixedAsset->fill($this->getData());

        if ($this->changesSchedule($fixedAsset)) {
            $this->ensureNothingBooked($fixedAsset);

            $schedule = $this->validateFixedAsset($fixedAsset, []);
            $fixedAsset->depreciations()->forceDelete();

            foreach ($schedule as $depreciation) {
                $fixedAsset->depreciations()->create($depreciation);
            }
        }

        if ($this->changesExternalDepreciation($fixedAsset)) {
            $this->ensureExternalDepreciationUnlocked($fixedAsset);
        }

        $fixedAsset->save();
        $fixedAsset->calculateBookValue()
            ->save();

        return $fixedAsset->withoutRelations()->fresh();
    }

    protected function changesSchedule(FixedAsset $fixedAsset): bool
    {
        foreach (['acquisition_cost', 'residual_value'] as $attribute) {
            if (bccomp(
                (string) $fixedAsset->getRawOriginal($attribute),
                (string) data_get($fixedAsset->getAttributes(), $attribute),
                2
            ) !== 0) {
                return true;
            }
        }

        return (int) $fixedAsset->getOriginal('useful_life_months') !== $fixedAsset->useful_life_months
            || $fixedAsset->getOriginal('depreciation_method_enum') !== $fixedAsset->depreciation_method_enum
            || $fixedAsset->getOriginal('depreciation_starts_at')->format('Y-m')
                !== $fixedAsset->depreciation_starts_at->format('Y-m');
    }

    protected function changesExternalDepreciation(FixedAsset $fixedAsset): bool
    {
        return $fixedAsset->getOriginal('externally_depreciated_until')?->toDateString()
            !== $fixedAsset->externally_depreciated_until?->toDateString();
    }

    protected function ensureExternalDepreciationUnlocked(FixedAsset $fixedAsset): void
    {
        if ($fixedAsset->hasBookedDepreciations()) {
            throw ValidationException::withMessages([
                'externally_depreciated_until' => [
                    __('The externally depreciated month cannot be changed once a depreciation has been booked.'),
                ],
            ]);
        }
    }

    protected function ensureNothingBooked(FixedAsset $fixedAsset): void
    {
        if ($fixedAsset->hasBookedDepreciations()) {
            throw ValidationException::withMessages([
                'id' => [__('The depreciation schedule cannot be changed once a depreciation has been booked.')],
            ]);
        }
    }

    protected function validateData(): void
    {
        parent::validateData();

        $fixedAsset = resolve_static(FixedAsset::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();
        $fixedAsset->fill($this->getData());

        $this->validateFixedAsset($fixedAsset, array_keys($this->getData()));

        if ($this->changesSchedule($fixedAsset)) {
            $this->ensureNothingBooked($fixedAsset);
        }

        if ($this->changesExternalDepreciation($fixedAsset)) {
            $this->ensureExternalDepreciationUnlocked($fixedAsset);
        }
    }
}
