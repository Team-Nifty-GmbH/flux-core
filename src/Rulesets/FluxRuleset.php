<?php

namespace FluxErp\Rulesets;

use FluxErp\Traits\HasAdditionalColumns;
use FluxErp\Traits\HasAttributeTranslations;

abstract class FluxRuleset
{
    protected static bool $addAdditionalColumnRules = true;

    protected static bool $addTranslationRules = true;

    protected static ?string $model = null;

    public static function getRules(): array
    {
        $rules = (new static())->rules();

        if (
            (static::$addAdditionalColumnRules || static::$addTranslationRules)
            && static::$model
        ) {
            $model = app(static::$model);

            if (in_array(HasAdditionalColumns::class, class_uses_recursive($model))) {
                $rules = array_merge($model->hasAdditionalColumnsValidationRules(), $rules);
            }

            if (in_array(HasAttributeTranslations::class, class_uses_recursive($model))) {
                $rules = array_merge($model->attributeTranslationRules(), $rules);
            }
        }

        return $rules;
    }

    abstract public function rules(): array;
}
