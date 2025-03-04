<?php

namespace FluxErp\Actions\AttributeTranslation;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AttributeTranslation;
use FluxErp\Rulesets\AttributeTranslation\UpdateAttributeTranslationRuleset;

class UpdateAttributeTranslation extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return UpdateAttributeTranslationRuleset::class;
    }

    public static function models(): array
    {
        return [AttributeTranslation::class];
    }

    public function performAction(): AttributeTranslation
    {
        $updateAttributeTranslation = resolve_static(AttributeTranslation::class, 'query')
            ->whereKey($this->getData('id'))
            ->first();
        $updateAttributeTranslation->fill($this->getData());
        $updateAttributeTranslation->save();

        return $updateAttributeTranslation->withoutRelations()->fresh();
    }
}
