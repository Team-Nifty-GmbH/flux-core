<?php

namespace FluxErp\Actions;

use FluxErp\Traits\Makeable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

    public static function name(): string
    {
        $exploded = explode('-', Str::kebab(class_basename(static::class)));
        $function = array_shift($exploded);

        return implode('-', $exploded) . '.' . $function;
    }

    public static function description(): string|null
    {
        return Str::of(class_basename(static::class))
            ->headline()
            ->lower()
            ->toString();
    }

    public function setData(array $data): static
    {
        $this->data = $data;

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
