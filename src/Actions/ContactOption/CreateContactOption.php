<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOption;
use FluxErp\Rulesets\ContactOption\CreateContactOptionRuleset;

class CreateContactOption extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateContactOptionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function performAction(): ContactOption
    {
        $contactOption = app(ContactOption::class, ['attributes' => $this->data]);
        $contactOption->save();

        return $contactOption->fresh();
    }
}
