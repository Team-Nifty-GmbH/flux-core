<?php

namespace FluxErp\Actions\AbsenceType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceType;
use FluxErp\Models\Client;
use FluxErp\Rulesets\AbsenceType\CreateAbsenceTypeRuleset;
use Illuminate\Support\Arr;

class CreateAbsenceType extends FluxAction
{
    public static function models(): array
    {
        return [AbsenceType::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateAbsenceTypeRuleset::class;
    }

    public function performAction(): AbsenceType
    {
        $data = $this->getData();
        $absencePolicies = Arr::pull($data, 'absence_policies');

        $absenceType = app(AbsenceType::class, ['attributes' => $data]);
        $absenceType->save();

        if ($absencePolicies) {
            $absenceType->absencePolicies()->sync($absencePolicies);
        }

        return $absenceType->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['client_id'] ??= resolve_static(Client::class, 'default')->getKey();
    }

    protected function validateData(): void
    {
        parent::validateData();

        // Count how many affects_ fields are true
        $affectsCount = 0;
        if (($this->data['affects_sick'] ?? false) === true) {
            $affectsCount++;
        }
        if (($this->data['affects_vacation'] ?? false) === true) {
            $affectsCount++;
        }
        if (($this->data['affects_overtime'] ?? false) === true) {
            $affectsCount++;
        }

        // Only one can be true at a time
        if ($affectsCount > 1) {
            throw_validation('Only one of affects_sick, affects_vacation, or affects_overtime can be true at a time.');
        }
    }
}
