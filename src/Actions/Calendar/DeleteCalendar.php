<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Calendar;
use FluxErp\Rulesets\Calendar\DeleteCalendarRuleset;
use Illuminate\Validation\ValidationException;

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
