<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Calendar;
use FluxErp\Rulesets\Calendar\DeleteCalendarRuleset;
use Illuminate\Validation\ValidationException;

class DeleteCalendar extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteCalendarRuleset::class;
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(Calendar::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if (resolve_static(Calendar::class, 'query')
            ->where('parent_id', $this->data['id'])
            ->exists()
        ) {
            throw ValidationException::withMessages([
                'id' => [__('Cannot delete a calendar that has children.')],
            ])->errorBag('deleteCalendar');
        }
    }
}
