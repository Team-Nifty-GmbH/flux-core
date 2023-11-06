<?php

namespace FluxErp\Actions\FormBuilderForm;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\ResponseHelper;
use FluxErp\Models\FormBuilderForm;

class DeleteFormBuilderForm extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:form_builder_forms,id',
        ];
    }

    public static function models(): array
    {
        return [FormBuilderForm::class];
    }

    public function performAction(): ?bool
    {
        return FormBuilderForm::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function validateData(): void
    {
        $validator = Validator($this->data, $this->rules);
        $validator->addModel(new FormBuilderForm());

        $this->data = $validator->validate();
    }
}
