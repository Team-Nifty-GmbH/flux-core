<?php

namespace FluxErp\Actions\FormBuilderForm;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateFormBuilderFormRequest;
use FluxErp\Models\FormBuilderForm;
use Illuminate\Support\Str;

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
        $formBuilderForm = FormBuilderForm::query()
            ->whereKey($this->data['id'])
            ->first();

        $this->data['slug'] = Str::slug($this->data['slug'] ?? $this->data['name']);

        $formBuilderForm->fill($this->data);
        $formBuilderForm->save();

        return $formBuilderForm->refresh();
    }
}
