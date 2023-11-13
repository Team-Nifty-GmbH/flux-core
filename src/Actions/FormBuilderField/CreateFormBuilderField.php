<?php

namespace FluxErp\Actions\FormBuilderField;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateFormBuilderFieldRequest;
use FluxErp\Models\FormBuilderField;

class CreateFormBuilderField extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateFormBuilderFieldRequest())->rules();
    }

    public static function models(): array
    {
        return [FormBuilderField::class];
    }

    public function performAction(): FormBuilderField
    {
        $formBuilderField = new FormBuilderField();

        $formBuilderField->fill($this->data);
        $formBuilderField->save();

        return $formBuilderField->refresh();
    }
}
