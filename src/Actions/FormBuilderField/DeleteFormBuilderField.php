<?php

namespace FluxErp\Actions\FormBuilderField;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderField;

class DeleteFormBuilderField extends FluxAction
{
    protected function  boot(array $boot): void
    {
        parent::boot($boot);
        $this->rules = [
            'id' => 'required|integer|exists:form_builder_fields,id',
        ];
    }

    public static function models(): array
    {
        return [
            FormBuilderField::class,
        ];
    }

    public function performAction(): ?bool
    {
        return FormBuilderField::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        $validator = Validator($this->data, $this->rules);
        $validator->addModel(new FormBuilderField());

        $this->data = $validator->validate();
    }
}
