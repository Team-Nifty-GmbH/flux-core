<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\WorkTime;
use FluxErp\Rules\ModelExists;

class CreateLockedWorkTimeRuleset extends CreateWorkTimeRuleset
{
    public function rules(): array
    {

        return array_merge(
            parent::rules(),
            ['started_at' => 'required_with:ended_at|date_format:Y-m-d H:i:s|before:now',
                'ended_at' => 'date_format:Y-m-d H:i:s|after:started_at',
                'parent_id' => [
                    'nullable',
                    'integer',
                    new ModelExists(WorkTime::class),
                ],
                'paused_time_ms' => 'integer|nullable|min:0',
                'total_time_ms' => 'integer|min:0',
            ]
        );
    }
}
