<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateWorkTimeRequest;
use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Model;

class UpdateWorkTime extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateWorkTimeRequest())->rules();
    }

    public static function models(): array
    {
        return [WorkTime::class];
    }

    public function performAction(): Model
    {
        $workTime = WorkTime::query()
            ->whereKey($this->data['id'])
            ->first();

        $workTime->fill($this->data);
        $workTime->save();

        return $workTime->withoutRelations()->fresh();
    }
}
