<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateCalendarRequest;
use FluxErp\Models\Calendar;
use Illuminate\Database\Eloquent\Model;

class UpdateCalendar extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateCalendarRequest())->rules();
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function execute(): Model
    {
        $calendar = Calendar::query()
            ->whereKey($this->data['id'])
            ->first();

        $calendar->fill($this->data);
        $calendar->save();

        return $calendar->withoutRelations()->fresh();
    }
}
