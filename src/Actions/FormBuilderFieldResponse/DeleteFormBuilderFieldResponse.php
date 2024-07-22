<?php

namespace FluxErp\Actions\FormBuilderFieldResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Rulesets\FormBuilderFieldResponse\DeleteFormBuilderFieldResponseRuleset;

class DeleteFormBuilderFieldResponse extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteFormBuilderFieldResponseRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [FormBuilderFieldResponse::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(FormBuilderFieldResponse::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
