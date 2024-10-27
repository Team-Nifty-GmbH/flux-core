<?php

namespace FluxErp\Actions\Schedule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Schedule;
use FluxErp\Rulesets\Schedule\DeleteScheduleRuleset;

class DeleteSchedule extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteScheduleRuleset::class;
    }

    public static function models(): array
    {
        return [Schedule::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Schedule::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
