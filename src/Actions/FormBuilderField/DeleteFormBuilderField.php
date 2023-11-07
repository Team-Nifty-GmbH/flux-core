<?php

namespace FluxErp\Actions\FormBuilderField;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderField;

class DeleteFormBuilderField extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:form_builder_fields,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [FormBuilderField::class];
    }

    public function performAction(): ?bool
    {
        return FormBuilderField::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
