<?php

namespace FluxErp\Actions\FixedAsset;

use FluxErp\Actions\FluxAction;
use FluxErp\Enums\DepreciationMethodEnum;
use FluxErp\Models\FixedAsset;
use FluxErp\Models\Tenant;
use FluxErp\Rulesets\FixedAsset\CreateFixedAssetRuleset;
use FluxErp\Traits\Action\ValidatesFixedAsset;

class CreateFixedAsset extends FluxAction
{
    use ValidatesFixedAsset;

    protected array $schedule = [];

    public static function models(): array
    {
        return [FixedAsset::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateFixedAssetRuleset::class;
    }

    public function performAction(): FixedAsset
    {
        $fixedAsset = app(FixedAsset::class, ['attributes' => $this->getData()]);
        $fixedAsset->save();

        foreach ($this->schedule as $depreciation) {
            $fixedAsset->depreciations()->create($depreciation);
        }

        $fixedAsset->calculateBookValue()
            ->save();

        return $fixedAsset->refresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['tenant_id'] ??= resolve_static(Tenant::class, 'default')->getKey();
        $this->data['depreciation_method_enum'] ??= DepreciationMethodEnum::Linear->value;
        $this->data['depreciation_starts_at'] ??= $this->getData('acquired_at');
        $this->data['residual_value'] ??= 0;
    }

    protected function validateData(): void
    {
        parent::validateData();

        $this->schedule = $this->validateFixedAsset(
            app(FixedAsset::class, ['attributes' => $this->getData()]),
            array_keys($this->getData())
        );
    }
}
