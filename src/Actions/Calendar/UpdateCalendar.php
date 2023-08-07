<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateCalendarRequest;
use FluxErp\Models\Calendar;
use Illuminate\Database\Eloquent\Model;

class UpdateCalendar extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateCalendarRequest())->rules();
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function performAction(): Model
    {
        $calendar = Calendar::query()
            ->whereKey($this->data['id'])
            ->first();

        $calendar->fill($this->data);
        $calendar->save();

        return $calendar->withoutRelations()->fresh();
    }
}
