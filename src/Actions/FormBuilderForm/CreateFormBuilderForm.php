<?php

namespace FluxErp\Actions\FormBuilderForm;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateFormBuilderFormRequest;
use FluxErp\Models\FormBuilderForm;
use Str;

class CreateFormBuilderForm extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateFormBuilderFormRequest())->rules();
    }

    public static function models(): array
    {
        return [FormBuilderForm::class];
    }

    public function performAction(): FormBuilderForm
    {
        $formBuilderForm = new FormBuilderForm();

        $this->data['slug'] = Str::slug($this->data['slug'] ?? $this->data['name']);

        $formBuilderForm->fill($this->data);
        $formBuilderForm->save();

        return $formBuilderForm->refresh();
    }
}
