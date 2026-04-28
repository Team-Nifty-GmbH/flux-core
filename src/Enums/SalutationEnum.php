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

    public static function salutation(string $case, object|array $address, ?string $locale = null): string
    {
        $parameter = [
            'firstname' => data_get($address, 'firstname'),
            'lastname' => data_get($address, 'lastname'),
            'company' => data_get($address, 'company'),
        ];

        $form = data_get($address, 'has_formal_salutation') ? 'formal' : 'informal';
        $suffix = match ($case) {
            SalutationEnum::Mrs => 'mrs',
            SalutationEnum::Mr => 'mr',
            SalutationEnum::Company => 'company',
            SalutationEnum::Family => 'family',
            default => 'no_salutation',
        };

        return __("salutation.{$form}.{$suffix}", $parameter, $locale);
    }

    public function get(Model $model, string $key, mixed $value, array $attributes): ?object
    {
        if (is_null($value)) {
            return null;
        }

        return static::tryFrom($value) ?? (object) ['name' => $value, 'value' => $value];
    }
}
