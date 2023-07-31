<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateCalendarRequest;
use FluxErp\Models\Calendar;

class CreateCalendar extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateCalendarRequest())->rules();
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function performAction(): Calendar
    {
        $calendar = new Calendar($this->data);
        $calendar->save();

        return $calendar;
    }
}
