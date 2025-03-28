<?php

namespace FluxErp\Actions\FormBuilderSection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\FormBuilderSection;
use FluxErp\Rulesets\FormBuilderSection\DeleteFormBuilderSectionRuleset;

class DeleteFormBuilderSection extends FluxAction
{
    public static function models(): array
    {
        return [FormBuilderSection::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteFormBuilderSectionRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(FormBuilderSection::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
