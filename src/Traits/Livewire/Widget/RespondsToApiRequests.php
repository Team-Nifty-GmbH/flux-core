<?php

namespace FluxErp\Traits\Livewire\Widget;

use Illuminate\Http\JsonResponse;
use ReflectionClass;
use ReflectionProperty;

trait RespondsToApiRequests
{
    /**
     * Public widget properties that are framework/context state, not part of
     * the computed result.
     */
    protected array $apiResponseExcept = [
        'config',
        'dashboardComponent',
        'employeeId',
        'options',
        'timeFrame',
        'userId',
        'widgetId',
    ];

    public function __invoke(): JsonResponse
    {
        $this->fillApiContext();

        return response()->json(['data' => $this->toApiResponse()]);
    }

    public function toApiResponse(): array
    {
        $this->mount();

        $data = [];
        $reflection = new ReflectionClass(static::class);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();

            if (in_array($name, $this->apiResponseExcept, true)) {
                continue;
            }

            $data[$name] = $this->{$name};
        }

        return array_filter($data, fn (mixed $value): bool => ! is_null($value));
    }

    protected function fillApiContext(): void
    {
        $user = auth()->user();

        if (property_exists($this, 'employeeId')) {
            $this->employeeId = $user?->employee?->getKey();
        }

        if (property_exists($this, 'userId')) {
            $this->userId = $user?->getKey();
        }
    }
}
