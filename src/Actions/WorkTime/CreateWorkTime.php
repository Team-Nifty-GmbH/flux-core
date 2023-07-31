<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateWorkTimeRequest;
use FluxErp\Models\WorkTime;

class CreateWorkTime extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateWorkTimeRequest())->rules();
    }

    public static function models(): array
    {
        return [WorkTime::class];
    }

    public function execute(): WorkTime
    {
        $workTime = new WorkTime($this->data);
        $workTime->save();

        return $workTime->fresh();
    }
}
