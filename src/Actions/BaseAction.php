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
        $this->data = $data[0] ?? [];
    }

    public function checkPermission(): static
    {
        if (! auth()->user()->hasPermissionTo('action.' . static::name())) {
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

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
