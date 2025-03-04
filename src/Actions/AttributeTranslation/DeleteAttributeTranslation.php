<?php

namespace FluxErp\Actions\AttributeTranslation;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AttributeTranslation;
use FluxErp\Rulesets\AttributeTranslation\DeleteAttributeTranslationRuleset;

class DeleteAttributeTranslation extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteAttributeTranslationRuleset::class;
    }

    public static function models(): array
    {
        return [AttributeTranslation::class];
    }

    public function performAction(): bool
    {
        return resolve_static(AttributeTranslation::class, 'query')
            ->whereKey($this->getData('id'))
            ->first()
            ->delete();
    }
}
