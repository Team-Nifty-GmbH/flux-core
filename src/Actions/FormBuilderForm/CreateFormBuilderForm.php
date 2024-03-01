<?php

namespace FluxErp\Actions\FormBuilderForm;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Rulesets\FormBuilderForm\CreateFormBuilderFormRuleset;
use Illuminate\Support\Str;

class CreateFormBuilderForm extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateFormBuilderFormRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [FormBuilderForm::class];
    }

    public function performAction(): FormBuilderForm
    {
        $this->data['slug'] = Str::slug($this->data['slug'] ?? $this->data['name']);

        $formBuilderForm = app(FormBuilderForm::class, ['attributes' => $this->data]);
        $formBuilderForm->save();

        return $formBuilderForm->refresh();
    }
}
