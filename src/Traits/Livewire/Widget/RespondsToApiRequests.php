<?php

namespace FluxErp\Traits\Livewire\Widget;

use ReflectionClass;
use ReflectionProperty;
use function Livewire\trigger;

trait RespondsToApiRequests
{
    protected array $apiResponseExcept = [
        'config',
        'dashboardComponent',
        'options',
        'widgetId',
    ];

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
            $name = $property->getName();

            if (in_array($name, $this->apiResponseExcept, true)) {
                continue;
            }

            $data[$name] = $this->{$name};
        }

        return array_filter($data, fn (mixed $value): bool => ! is_null($value));
    }

    protected function apiRules(): array
    {
        return [];
    }
}
