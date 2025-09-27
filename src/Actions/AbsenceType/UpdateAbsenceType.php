<?php

namespace FluxErp\Actions\AbsenceType;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsenceType;
use FluxErp\Rulesets\AbsenceType\UpdateAbsenceTypeRuleset;
use Illuminate\Support\Arr;

class UpdateAbsenceType extends FluxAction
{
    public static function models(): array
    {
        return [AbsenceType::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateAbsenceTypeRuleset::class;
    }

    public function performAction(): AbsenceType
    {
        $absenceType = resolve_static(AbsenceType::class, 'query')
            ->whereKey($this->getData('id'))
            ->firstOrFail();

        $data = $this->getData();
        $absencePolicies = Arr::pull($data, 'absence_policies');

        $absenceType->fill($data);
        $absenceType->save();

        if (! is_null($absencePolicies)) {
            $absenceType->absencePolicies()->sync($absencePolicies);
        }

        return $absenceType->withoutRelations()->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->rules = array_merge(
            $this->rules,
            [
                'code' => $this->rules['code'] . ',' . ($this->data['id'] ?? 0),
            ]
        );
    }

    protected function validateData(): void
    {
        parent::validateData();

        // TODO: Validate affects flags
    }
}
