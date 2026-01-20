<?php

namespace FluxErp\Rules;

use BackedEnum;
use FluxErp\Support\Enums\FluxEnum;
use Illuminate\Validation\Rules\Enum as BaseEnumRule;
use UnitEnum;

class EnumRule extends BaseEnumRule
{
    public function __toString(): string
    {
        $cases = ! empty($this->only)
            ? $this->only
            : array_filter($this->type::cases(), fn ($case) => ! in_array($case, $this->except, true));

        $values = array_map(function ($case) {
            $value = match (true) {
                $case instanceof BackedEnum, property_exists($case, 'value') => $case->value,
                $case instanceof UnitEnum => $case->name,
                default => $case,
            };

            return '"' . str_replace('"', '""', (string) $value) . '"';
        }, $cases);

        return 'in:' . implode(',', $values);
    }

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
