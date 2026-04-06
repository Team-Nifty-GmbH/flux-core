<?php

namespace FluxErp\Traits\Action;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Assert;

trait SupportsTesting
{
    public static function testCreate(array $data): Model
    {
        return static::make($data)
            ->checkPermission()
            ->validate()
            ->execute();
    }

    public static function assertValidationErrors(array $data, array|string $expectedKeys): void
    {
        $keys = Arr::wrap($expectedKeys);

        try {
            static::make($data)->validate();
            Assert::fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $errorKeys = array_keys($e->errors());

            foreach ($keys as $key) {
                Assert::assertContains(
                    $key,
                    $errorKeys,
                    "Expected validation error for '{$key}' but got: " . implode(', ', $errorKeys)
                );
            }
        }
    }

    public static function assertValidationPasses(array $data): static
    {
        try {
            $action = static::make($data)->validate();
        } catch (ValidationException $e) {
            Assert::fail('Validation failed unexpectedly: ' . json_encode($e->errors()));
        }

        Assert::assertTrue(true);

        return $action;
    }
}
