<?php

namespace FluxErp\Actions\Calendar;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Calendar;

class DeleteCalendar extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:calendars,id',
        ];
    }

    public static function models(): array
    {
        return [Calendar::class];
    }

    public function execute(): ?bool
    {
        return Calendar::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
