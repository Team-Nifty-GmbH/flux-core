<?php

namespace FluxErp\Actions\FormBuilderField;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderField;
use FluxErp\Rulesets\FormBuilderField\DeleteFormBuilderFieldRuleset;

class DeleteFormBuilderField extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteFormBuilderFieldRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [FormBuilderField::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(FormBuilderField::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
