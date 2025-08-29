<?php

namespace FluxErp\Actions\VacationBlackout;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VacationBlackout;
use FluxErp\Rulesets\VacationBlackout\CreateVacationBlackoutRuleset;

class CreateVacationBlackout extends FluxAction
{
    public static function models(): array
    {
        return [VacationBlackout::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateVacationBlackoutRuleset::class;
    }

    public function performAction(): VacationBlackout
    {
        $data = $this->getData();
        $roleIds = data_get($data, 'role_ids', []);
        $userIds = data_get($data, 'user_ids', []);

        unset($data['role_ids'], $data['user_ids']);

        $vacationBlackout = app(VacationBlackout::class, ['attributes' => $data]);
        $vacationBlackout->save();

        if ($roleIds) {
            $vacationBlackout->roles()->sync($roleIds);
        }

        if ($userIds) {
            $vacationBlackout->users()->sync($userIds);
        }

        return $vacationBlackout->fresh()->load(['roles', 'users']);
    }
}
