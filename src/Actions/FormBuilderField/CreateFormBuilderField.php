<?php

namespace FluxErp\Actions\FormBuilderField;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderField;
use FluxErp\Rulesets\FormBuilderField\CreateFormBuilderFieldRuleset;

class CreateFormBuilderField extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateFormBuilderFieldRuleset::class, 'getRules');
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
