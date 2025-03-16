<?php

namespace FluxErp\Actions\FormBuilderField;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderField;
use FluxErp\Rulesets\FormBuilderField\DeleteFormBuilderFieldRuleset;

class DeleteFormBuilderField extends FluxAction
{
    public static function models(): array
    {
        return [FormBuilderField::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteFormBuilderFieldRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(FormBuilderField::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
