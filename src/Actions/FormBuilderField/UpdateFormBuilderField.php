<?php

namespace FluxErp\Actions\FormBuilderField;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateFormBuilderFieldRequest;
use FluxErp\Models\FormBuilderField;

class UpdateFormBuilderField extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateFormBuilderFieldRequest())->rules();
    }

    public static function models(): array
    {
        return [FormBuilderField::class];
    }

    public function performAction(): FormBuilderField
    {
        $formBuilderField = FormBuilderField::find($this->data['id']);
        $formBuilderField->fill($this->data);
        $formBuilderField->save();

        return $formBuilderField->refresh();
    }
}
