<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;

class SalutationEnum extends FluxEnum
{
    use EnumTrait;

    final public const string Company = 'company';

    final public const string Family = 'family';

    final public const string Mr = 'mr';

    final public const string Mrs = 'mrs';

    final public const string NoSalutation = 'no_salutation';

    public static function gender(string $case): string
    {
        return match ($case) {
            SalutationEnum::Mrs => 'female',
            SalutationEnum::Mr => 'male',
            default => 'neutral',
        };
    }

    public static function salutation(string $case, object|array $address): string
    {
        $parameter = [
            'firstname' => data_get($address, 'firstname'),
            'lastname' => data_get($address, 'lastname'),
            'company' => data_get($address, 'company'),
        ];

        if (data_get($address, 'has_formal_salutation')) {
            return match ($case) {
                SalutationEnum::Mrs => __('salutation.formal.mrs', $parameter),
                SalutationEnum::Mr => __('salutation.formal.mr', $parameter),
                SalutationEnum::Company => __('salutation.formal.company', $parameter),
                SalutationEnum::Family => __('salutation.formal.family', $parameter),
                default => __('salutation.formal.no_salutation', $parameter),
            };
        } else {
            return match ($case) {
                SalutationEnum::Mrs => __('salutation.informal.mrs', $parameter),
                SalutationEnum::Mr => __('salutation.informal.mr', $parameter),
                SalutationEnum::Company => __('salutation.informal.company', $parameter),
                SalutationEnum::Family => __('salutation.informal.family', $parameter),
                default => __('salutation.informal.no_salutation', $parameter),
            };
        }
    }
}
