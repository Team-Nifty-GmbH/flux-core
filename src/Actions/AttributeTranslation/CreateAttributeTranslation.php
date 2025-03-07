<?php

namespace FluxErp\Actions\AttributeTranslation;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AttributeTranslation;
use FluxErp\Rulesets\AttributeTranslation\CreateAttributeTranslationRuleset;

class CreateAttributeTranslation extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateAttributeTranslationRuleset::class;
    }

    public static function models(): array
    {
        return [AttributeTranslation::class];
    }

    public function performAction(): AttributeTranslation
    {
        $attributeTranslation = app(AttributeTranslation::class, ['attributes' => $this->getData()]);
        $attributeTranslation->save();

        return $attributeTranslation->fresh();
    }
}
