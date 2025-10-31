<?php

namespace FluxErp\Rules;

use FluxErp\Support\Enums\FluxEnum;
use Illuminate\Validation\Rules\Enum as BaseEnumRule;

class EnumRule extends BaseEnumRule
{
    public function passes($attribute, $value): bool
    {
        $type = resolve_static($this->type, 'class');
        if (
            is_subclass_of(
                $type,
                resolve_static(FluxEnum::class, 'class')
            )
            || method_exists($type, 'tryFrom')
        ) {
            return ! is_null(resolve_static($type, 'tryFrom', ['value' => $value]));
        } else {
            return parent::passes($attribute, $value);
        }
    }
}
