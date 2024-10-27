<?php

namespace FluxErp\Actions\FormBuilderResponse;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderResponse;
use FluxErp\Rulesets\FormBuilderResponse\DeleteFormBuilderResponseRuleset;

class DeleteFormBuilderResponse extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return DeleteFormBuilderResponseRuleset::class;
    }

    public static function models(): array
    {
        return [FormBuilderResponse::class];
    }

    public function performAction(): ?bool
    {
        return resolve_static(FormBuilderResponse::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
