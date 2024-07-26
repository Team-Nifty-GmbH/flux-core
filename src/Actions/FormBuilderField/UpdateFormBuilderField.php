<?php

namespace FluxErp\Actions\FormBuilderField;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderField;
use FluxErp\Rulesets\FormBuilderField\UpdateFormBuilderFieldRuleset;

class UpdateFormBuilderField extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateFormBuilderFieldRuleset::class, 'getRules');
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
