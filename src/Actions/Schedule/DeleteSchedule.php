<?php

namespace FluxErp\Actions\Schedule;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Schedule;

class DeleteSchedule extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:schedules,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Schedule::class];
    }

    public function performAction(): ?bool
    {
        return Schedule::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
