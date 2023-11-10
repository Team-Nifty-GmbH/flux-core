<?php

namespace FluxErp\Actions\FormBuilderForm;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateFormBuilderFormRequest;
use FluxErp\Models\FormBuilderForm;
use Str;

class UpdateFormBuilderForm extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateFormBuilderFormRequest())->rules();
    }

    public static function models(): array
    {
        return [FormBuilderForm::class];
    }

    public function performAction(): FormBuilderForm
    {
        $formBuilderForm = FormBuilderForm::find($this->data['id']);

        $this->data['slug'] = $this->data['slug'] == null ?
            Str::slug($this->data['name']) :
            Str::slug($this->data['slug']);

        $formBuilderForm->fill($this->data);
        $formBuilderForm->save();

        return $formBuilderForm->refresh();
    }
}
