<?php

namespace FluxErp\Actions\AbsencePolicy;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AbsencePolicy;
use FluxErp\Models\Client;
use FluxErp\Rulesets\AbsencePolicy\CreateAbsencePolicyRuleset;

class CreateAbsencePolicy extends FluxAction
{
    public static function models(): array
    {
        return [AbsencePolicy::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateAbsencePolicyRuleset::class;
    }

    public function performAction(): AbsencePolicy
    {
        $absencePolicy = app(AbsencePolicy::class, ['attributes' => $this->data]);
        $absencePolicy->save();

        return $absencePolicy->fresh();
    }

    protected function prepareForValidation(): void
    {
        $this->data['client_id'] ??= resolve_static(Client::class, 'default')->getKey();
    }
}
