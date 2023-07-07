<?php

namespace FluxErp\Actions;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Traits\Makeable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BaseAction implements ActionInterface
{
    use Makeable;

    protected array $data;

    protected array $rules = [];

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

    public static function models(): array
    {
        return [];
    }

    public function execute()
    {
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
