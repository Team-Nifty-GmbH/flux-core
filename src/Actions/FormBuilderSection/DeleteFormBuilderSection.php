<?php

namespace FluxErp\Actions\FormBuilderSection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderSection;

class DeleteFormBuilderSection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'id' => 'required|integer|exists:form_builder_sections,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [FormBuilderSection::class];
    }

    public function performAction(): ?bool
    {
        return FormBuilderSection::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
