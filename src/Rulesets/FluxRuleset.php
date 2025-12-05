<?php

namespace FluxErp\Rulesets;

use FluxErp\Traits\Model\HasAttributeTranslations;

abstract class FluxRuleset
{
    protected static bool $addTranslationRules = true;

    protected static ?string $model = null;

    abstract public function rules(): array;

    public static function getRules(): array
    {
        $rules = (new static())->rules();

        if (static::$addTranslationRules && static::$model) {
            $model = app(static::$model);

            if (in_array(HasAttributeTranslations::class, class_uses_recursive($model))) {
                $rules = array_merge($model->attributeTranslationRules(), $rules);
            }
        }

        return $rules;
    }
}
