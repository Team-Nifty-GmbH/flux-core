<?php

namespace FluxErp\Enums;

use FluxErp\Enums\Traits\EnumTrait;
use FluxErp\Support\Enums\FluxEnum;
use Illuminate\Database\Eloquent\Model;

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

        $locale = data_get($address, 'language.language_code');

        if (data_get($address, 'has_formal_salutation')) {
            return match ($case) {
                SalutationEnum::Mrs => __('salutation.formal.mrs', $parameter, $locale),
                SalutationEnum::Mr => __('salutation.formal.mr', $parameter, $locale),
                SalutationEnum::Company => __('salutation.formal.company', $parameter, $locale),
                SalutationEnum::Family => __('salutation.formal.family', $parameter, $locale),
                default => __('salutation.formal.no_salutation', $parameter, $locale),
            };
        } else {
            return match ($case) {
                SalutationEnum::Mrs => __('salutation.informal.mrs', $parameter, $locale),
                SalutationEnum::Mr => __('salutation.informal.mr', $parameter, $locale),
                SalutationEnum::Company => __('salutation.informal.company', $parameter, $locale),
                SalutationEnum::Family => __('salutation.informal.family', $parameter, $locale),
                default => __('salutation.informal.no_salutation', $parameter, $locale),
            };
        }
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): ?object
    {
        if (is_null($value)) {
            return null;
        }

        return static::tryFrom($value) ?? (object) ['name' => $value, 'value' => $value];
    }
}
