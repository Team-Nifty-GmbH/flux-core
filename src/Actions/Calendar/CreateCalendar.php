<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateCalendarRequest;
use FluxErp\Models\Calendar;

class CreateCalendar extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateCalendarRequest())->rules();
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function execute(): Calendar
    {
        $calendar = new Calendar($this->data);
        $calendar->save();

        return $calendar->fresh();
    }
}
