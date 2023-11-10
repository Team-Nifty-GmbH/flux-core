<?php

namespace FluxErp\Actions\FormBuilderResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderResponse;

class DeleteFormBuilderResponse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|exists:form_builder_responses,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [FormBuilderResponse::class];
    }

    public function performAction(): ?bool
    {
        return FormBuilderResponse::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
