<?php

namespace FluxErp\Actions\FormBuilderForm;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateFormBuilderFormRequest;
use FluxErp\Models\FormBuilderForm;
use Illuminate\Support\Facades\Validator;
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
        return [
            FormBuilderForm::class,
        ];
    }

    public function performAction(): FormBuilderForm
    {
        $formBuilderForm = new FormBuilderForm();

        $this->data['slug'] = $this->data['slug'] == null ?
            Str::slug($this->data['name']) :
            Str::slug($this->data['slug']);

        $formBuilderForm->fill($this->data);
        $formBuilderForm->save();

        return $formBuilderForm->refresh();
    }

    public function validateData(): void
    {
        $validator = Validator($this->data, $this->rules);
        $validator->addModel(new FormBuilderForm());

        $this->data = $validator->validate();
    }
}
