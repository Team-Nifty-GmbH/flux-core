<?php

namespace FluxErp\Traits\Livewire\Widget;

use ReflectionClass;
use ReflectionProperty;
use function Livewire\trigger;

trait RespondsToApiRequests
{
    public function __invoke()
    {
        $name = app('livewire.finder')->normalizeName(static::class);
        $component = app('livewire')->new($name);

        foreach (request()->validate($component->apiRules()) as $parameter => $value) {
            $component->{$parameter} = $value;
        }

        trigger('mount', $component, [], null, null, []);

        return response()->json(['data' => $component->toApiResponse()]);
    }

    public function toApiResponse(): array
    {
        $data = [];

        foreach ((new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $data[$property->getName()] = $this->{$property->getName()};
        }

        return array_filter($data, fn (mixed $value): bool => ! is_null($value));
    }

    protected function apiRules(): array
    {
        return [];
    }
}
