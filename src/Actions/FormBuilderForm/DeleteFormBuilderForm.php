<?php

namespace FluxErp\Actions\FormBuilderForm;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderForm;
use FluxErp\Rulesets\FormBuilderForm\DeleteFormBuilderFormRuleset;

class DeleteFormBuilderForm extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteFormBuilderFormRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [FormBuilderForm::class];
    }

    public function performAction(): ?bool
    {
        return app(FormBuilderForm::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
