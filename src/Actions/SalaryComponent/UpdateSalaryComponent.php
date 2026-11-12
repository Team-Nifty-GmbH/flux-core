<?php

namespace FluxErp\Actions\SalaryComponent;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\SalaryComponent;
use FluxErp\Rulesets\SalaryComponent\UpdateSalaryComponentRuleset;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class UpdateSalaryComponent extends FluxAction
{
    public static function models(): array
    {
        return [SalaryComponent::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateSalaryComponentRuleset::class;
    }

    public function performAction(): SalaryComponent
    {
        $salaryComponent = resolve_static(SalaryComponent::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $salaryComponent->fill($this->getData());
        $salaryComponent->save();

        return $salaryComponent->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $salaryComponent = resolve_static(SalaryComponent::class, 'query')
            ->whereKey($this->getData('id'))
            ->first(['id', 'valid_from', 'valid_until']);

        $validFrom = $this->getData('valid_from', $salaryComponent->valid_from);
        $validUntil = $this->getData('valid_until', $salaryComponent->valid_until);

        if ($validFrom && $validUntil && Carbon::parse($validUntil)->lt(Carbon::parse($validFrom))) {
            throw ValidationException::withMessages([
                'valid_until' => ['The valid until date must be on or after the valid from date.'],
            ])
                ->errorBag('updateSalaryComponent');
        }
    }
}
