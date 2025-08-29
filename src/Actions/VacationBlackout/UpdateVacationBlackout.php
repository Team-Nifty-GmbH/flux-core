<?php

namespace FluxErp\Actions\VacationBlackout;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\VacationBlackout;
use FluxErp\Rulesets\VacationBlackout\UpdateVacationBlackoutRuleset;

class UpdateVacationBlackout extends FluxAction
{
    public static function models(): array
    {
        return [VacationBlackout::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateVacationBlackoutRuleset::class;
    }

    public function performAction(): VacationBlackout
    {
        $vacationBlackout = resolve_static(VacationBlackout::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();

        $data = $this->getData();
        $roleIds = data_get($data, 'role_ids');
        $userIds = data_get($data, 'user_ids');

        unset($data['role_ids'], $data['user_ids']);

        $vacationBlackout->fill($data);
        $vacationBlackout->save();

        if (! is_null($roleIds)) {
            $vacationBlackout->roles()->sync($roleIds);
        }

        if (! is_null($userIds)) {
            $vacationBlackout->users()->sync($userIds);
        }

        return $vacationBlackout->fresh()->load(['roles', 'users']);
    }
}
