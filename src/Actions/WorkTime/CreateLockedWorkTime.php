<?php

namespace FluxErp\Actions\WorkTime;

use FluxErp\Models\WorkTime;
use FluxErp\Rulesets\WorkTime\CreateLockedWorkTimeRuleset;

class CreateLockedWorkTime extends CreateWorkTime {


    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateLockedWorkTimeRuleset::class, 'getRules');
    }

    protected function prepareForValidation(): void
    {
        $this->data['user_id'] ??= auth()->user()->id;

        if (!is_null($this->data['user_id'])) {
//            WorkTime::query()
        }
    }

}
