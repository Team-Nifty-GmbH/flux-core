<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\User;
use FluxErp\Models\WorkTime;
use FluxErp\Rules\ModelExists;

class CreateLockedWorkTimeRuleset extends CreateWorkTimeRuleset
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'parent_id' => [
                    'nullable',
                    'integer',
                    app(ModelExists::class, ['model' => WorkTime::class]),
                ],
                'user_id' => [
                    'required',
                    'integer',
                    app(ModelExists::class, ['model' => User::class]),
                ],
                'started_at' => 'required|date',
                'ended_at' => 'nullable|date|after:started_at',
                'paused_time_ms' => 'integer|nullable|min:0',
            ]
        );
    }
}
