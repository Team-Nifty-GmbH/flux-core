<?php

namespace FluxErp\Support\Enums;

use FluxErp\Support\Enums\Interfaces\EnumInterface;
use FluxErp\Support\Enums\Traits\IsCastable;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use ReflectionClassConstant;
use ValueError;

abstract class FluxEnum implements EnumInterface, SerializesCastableAttributes
{
    use IsCastable;

    public static function cases(): array
    {
        return Cache::memo()->rememberForever(
            'flux.enums.' . resolve_static(static::class, 'class'),
            function (): array {
                $reflection = new ReflectionClass(resolve_static(static::class, 'class'));

                return Arr::mapWithKeys(
                    $reflection->getConstants(ReflectionClassConstant::IS_FINAL),
                    fn (int|string $value, string $key) => [
                        $key => (object) [
                            'name' => $key,
                            'value' => $value,
                        ],
                    ],
                );
            }
        );
    }

    public static function from(int|string $value): object
    {
        $case = array_find(static::cases(), fn ($case) => $case->value === $value);

        if (is_null($case)) {
            throw new ValueError('"' . $value . '" is not a valid backing value for enum ' . static::class);
        }

        return $case;
    }

    public static function tryFrom(int|string|null $value): ?object
    {
        if (is_null($value)) {
            return null;
        }

        try {
            return static::from($value);
        } catch (ValueError) {
            return null;
        }
    }

    public function serialize(Model $model, string $key, mixed $value, array $attributes): string|int|null
    {
        return $value?->value;
    }
}
