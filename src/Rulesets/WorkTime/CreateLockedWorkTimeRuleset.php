<?php

namespace FluxErp\Rulesets\WorkTime;

use FluxErp\Models\WorkTime;
use FluxErp\Rules\ModelExists;

class CreateLockedWorkTimeRuleset extends CreateWorkTimeRuleset {

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['ended_at'] = 'date_format:Y-m-d H:i:s|after:started_at';
        $rules['parent_id'] = [
            'nullable',
            'integer',
            new ModelExists(WorkTime::class),
        ];

        return  $rules;
    }
}
