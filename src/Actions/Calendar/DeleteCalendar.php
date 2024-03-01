<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Calendar;
use FluxErp\Rulesets\Calendar\DeleteCalendarRuleset;

class DeleteCalendar extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteCalendarRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function performAction(): ?bool
    {
        return app(Calendar::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
