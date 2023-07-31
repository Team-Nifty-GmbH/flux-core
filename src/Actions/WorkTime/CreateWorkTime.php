<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateWorkTimeRequest;
use FluxErp\Models\WorkTime;

class CreateWorkTime extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateWorkTimeRequest())->rules();
    }

    public static function models(): array
    {
        return [WorkTime::class];
    }

    public function performAction(): WorkTime
    {
        $workTime = new WorkTime($this->data);
        $workTime->save();

        return $workTime->fresh();
    }
}
