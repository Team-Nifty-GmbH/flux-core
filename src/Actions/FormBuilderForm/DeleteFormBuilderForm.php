<?php

namespace FluxErp\Actions\FormBuilderForm;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Rulesets\FormBuilderForm\DeleteFormBuilderFormRuleset;

class DeleteFormBuilderForm extends FluxAction
{
    public static function models(): array
    {
        return [FormBuilderForm::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteFormBuilderFormRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(FormBuilderForm::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
