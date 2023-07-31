<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Calendar;

class DeleteCalendar extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:calendars,id',
        ];
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function performAction(): ?bool
    {
        return Calendar::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
