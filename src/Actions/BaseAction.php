<?php

namespace FluxErp\Actions;

use FluxErp\Traits\Makeable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Exceptions\UnauthorizedException;

abstract class BaseAction
{
    use Makeable;

    protected array $data;

    protected array $rules = [];

    abstract public function execute();

    abstract public static function models(): array;

    public function __construct(array $data)
    {
        $this->setData($data[0] ?? [],  $data[1] ?? false);
    }

    public function checkPermission(): static
    {
        if (! auth()->user()->can('action.' . static::name())) {
            throw UnauthorizedException::forPermissions(['action.' . static::name()]);
        }

        return $this;
    }

    public static function name(): string
    {
        $exploded = explode('-', Str::kebab(class_basename(static::class)));
        $function = array_shift($exploded);

        return implode('-', $exploded) . '.' . $function;
    }

    public static function description(): ?string
    {
        return Str::of(class_basename(static::class))
            ->headline()
            ->lower()
            ->toString();
    }

    public function setData(array $data, bool $keepEmptyStrings = false): static
    {
        $this->data = $keepEmptyStrings ? $data : $this->convertEmptyStringToNull($data);

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function convertEmptyStringToNull(array $data): array
    {
        return array_map(function ($value) {
            if (is_array($value)) {
                return $this->convertEmptyStringToNull($value); // Recurse into sub-arrays
            }

            return $value === '' ? null : $value;
        }, $data);
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
