<?php

namespace FluxErp\Actions\ContactOption;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactOption;
use FluxErp\Rulesets\ContactOption\DeleteContactOptionRuleset;

class DeleteContactOption extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteContactOptionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ContactOption::class];
    }

    public function performAction(): ?bool
    {
        return app(ContactOption::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
