<?php

namespace FluxErp\Actions\FormBuilderField;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderField;
use FluxErp\Rulesets\FormBuilderField\UpdateFormBuilderFieldRuleset;

class UpdateFormBuilderField extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateFormBuilderFieldRuleset::class;
    }

    public static function models(): array
    {
        return [FormBuilderField::class];
    }

    public function performAction(): FormBuilderField
    {
        $formBuilderField = resolve_static(FormBuilderField::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $formBuilderField->fill($this->data);
        $formBuilderField->save();

        return $formBuilderField->refresh();
    }
}
