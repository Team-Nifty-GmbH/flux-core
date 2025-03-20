<?php

namespace FluxErp\Actions\AttributeTranslation;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\AttributeTranslation;
use FluxErp\Rulesets\AttributeTranslation\UpsertAttributeTranslationRuleset;
use Illuminate\Database\Eloquent\Builder;

class UpsertAttributeTranslation extends FluxAction
{
    public static function models(): array
    {
        return [AttributeTranslation::class];
    }

    protected function getRulesets(): string|array
    {
        return UpsertAttributeTranslationRuleset::class;
    }

    public function performAction(): AttributeTranslation
    {
        $attributeTranslation = resolve_static(AttributeTranslation::class, 'query')
            ->when(
                $this->getData('id'),
                fn (Builder $query) => $query->whereKey($this->getData('id')),
                fn (Builder $query) => $query
                    ->where('language_id', $this->getData('language_id'))
                    ->where('model_type', $this->getData('model_type'))
                    ->where('model_id', $this->getData('model_id'))
                    ->where('attribute', $this->getData('attribute'))
            )
            ->firstOrNew();
        $attributeTranslation->fill($this->getData());
        $attributeTranslation->save();

        return $attributeTranslation->withoutRelations()->fresh();
    }
}
