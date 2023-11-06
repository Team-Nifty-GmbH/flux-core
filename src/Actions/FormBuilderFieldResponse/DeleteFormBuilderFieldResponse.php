<?php

namespace FluxErp\Actions\FormBuilderFieldResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderFieldResponse;

class DeleteFormBuilderFieldResponse extends FluxAction
{
    protected function  boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|exists:form_builder_field_responses,id',
        ];
    }
    public static function models(): array
    {
        return [
            FormBuilderFieldResponse::class,
        ];
    }

    public function performAction(): ?bool
    {
        return FormBuilderFieldResponse::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        $validator = Validator($this->data, $this->rules);
        $validator->addModel(new FormBuilderFieldResponse());

        $this->data = $validator->validate();
    }
}
