<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateWorkTimeRequest;
use FluxErp\Models\WorkTime;
use Illuminate\Database\Eloquent\Model;

class UpdateWorkTime extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateWorkTimeRequest())->rules();
    }

    public static function models(): array
    {
        return [WorkTime::class];
    }

    public function execute(): Model
    {
        $workTime = WorkTime::query()
            ->whereKey($this->data['id'])
            ->first();

        $workTime->fill($this->data);
        $workTime->save();

        return $workTime->withoutRelations()->fresh();
    }
}
