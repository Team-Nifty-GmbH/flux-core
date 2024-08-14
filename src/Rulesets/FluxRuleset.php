<?php

namespace FluxErp\Rulesets;

use FluxErp\Traits\HasAdditionalColumns;

abstract class FluxRuleset
{
    protected static ?string $model = null;

    protected static bool $addAdditionalColumnRules = true;

    abstract public function rules(): array;

    public static function getRules(): array
    {
        $rules = (new static)->rules();

        if (static::$addAdditionalColumnRules && static::$model) {
            $model = app(static::$model);

            if (in_array(HasAdditionalColumns::class, class_uses_recursive($model))) {
                $rules = array_merge($model->hasAdditionalColumnsValidationRules(), $rules);
            }
        }

        return $rules;
    }
}
