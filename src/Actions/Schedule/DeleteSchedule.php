<?php

namespace FluxErp\Actions\Schedule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Schedule;
use FluxErp\Rulesets\Schedule\DeleteScheduleRuleset;

class DeleteSchedule extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteScheduleRuleset::class, 'getRules');
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
