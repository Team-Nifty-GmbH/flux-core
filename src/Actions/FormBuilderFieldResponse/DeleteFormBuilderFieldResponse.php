<?php

namespace FluxErp\Actions\FormBuilderFieldResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderFieldResponse;
use FluxErp\Rulesets\FormBuilderFieldResponse\DeleteFormBuilderFieldResponseRuleset;

class DeleteFormBuilderFieldResponse extends FluxAction
{
    public static function models(): array
    {
        return [FormBuilderFieldResponse::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteFormBuilderFieldResponseRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(FormBuilderFieldResponse::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
