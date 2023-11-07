<?php

namespace FluxErp\Actions\FormBuilderForm;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderForm;

class DeleteFormBuilderForm extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:form_builder_forms,id,deleted_at,NULL',
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
}
