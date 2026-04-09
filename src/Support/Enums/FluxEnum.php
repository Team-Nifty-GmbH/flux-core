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
        $cacheKey = 'flux.enums.' . resolve_static(static::class, 'class');

        $cases = Cache::memo()->rememberForever(
            $cacheKey,
            function (): array {
                $reflection = new ReflectionClass(resolve_static(static::class, 'class'));

                return Arr::mapWithKeys(
                    $reflection->getConstants(ReflectionClassConstant::IS_FINAL),
                    fn (int|string|array $value, string $key) => [
                        $key => ['name' => $key, 'value' => $value],
                    ],
                );
            }
        );

        // Fallback: stale cache may contain stdClass objects from before this fix
        if (! empty($cases) && ! is_array(Arr::first($cases))) {
            Cache::memo()->forget($cacheKey);

            $reflection = new ReflectionClass(resolve_static(static::class, 'class'));
            $cases = Arr::mapWithKeys(
                $reflection->getConstants(ReflectionClassConstant::IS_FINAL),
                fn (int|string|array $value, string $key) => [
                    $key => ['name' => $key, 'value' => $value],
                ],
            );
        }

        return array_map(fn (array $case) => (object) $case, $cases);
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
