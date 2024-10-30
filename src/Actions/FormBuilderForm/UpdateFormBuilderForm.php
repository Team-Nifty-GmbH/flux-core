<?php

namespace FluxErp\Actions\FormBuilderForm;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Rulesets\FormBuilderForm\UpdateFormBuilderFormRuleset;
use Illuminate\Support\Str;

class UpdateFormBuilderForm extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateFormBuilderFormRuleset::class;
    }

    public static function models(): array
    {
        return [FormBuilderForm::class];
    }

    public function performAction(): FormBuilderForm
    {
        $this->data['slug'] = Str::slug($this->data['slug'] ?? $this->data['name']);

        $formBuilderForm = resolve_static(FormBuilderForm::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $formBuilderForm->fill($this->data);
        $formBuilderForm->save();

        return $formBuilderForm->refresh();
    }
}
