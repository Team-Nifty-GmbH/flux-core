<?php

namespace FluxErp\Actions\FormBuilderField;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderField;
use FluxErp\Rulesets\FormBuilderField\CreateFormBuilderFieldRuleset;

class CreateFormBuilderField extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return CreateFormBuilderFieldRuleset::class;
    }

    public static function models(): array
    {
        return [FormBuilderField::class];
    }

    public function performAction(): FormBuilderField
    {
        $formBuilderField = app(FormBuilderField::class, ['attributes' => $this->data]);
        $formBuilderField->save();

        return $formBuilderField->refresh();
    }
}
