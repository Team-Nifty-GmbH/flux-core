<?php

namespace FluxErp\Support\Metrics\Results;

use ArrayAccess;
use Exception;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Response;

class Result implements ArrayAccess, Responsable
{
    public array $container;

    public function __construct(
        protected array $data,
        protected array $labels,
        protected null|float|array $growthRate
    ) {
        $this->container = [$labels, $data, $growthRate];
    }

    public static function make(array $data, array $labels, null|float|array $growthRate): static
    {
        return app(static::class, [
            'data' => $data,
            'labels' => $labels,
            'growthRate' => $growthRate,
        ]);
    }

    public function getOptions(): array
    {
        return array_combine($this->labels, $this->data);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function getCombinedData(): array
    {
        return array_combine($this->labels, $this->data);
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function setLabels(array $labels): static
    {
        $this->labels = $labels;

        return $this;
    }

    public function mergeLabels(array $labels, float|int $default = 0): static
    {
        $data = $this->getCombinedData();

        foreach ($labels as $label) {
            $data[$label] = $data[$label] ?? $default;
        }
        ksort($data);

        return $this->setData(array_values($data))->setLabels(array_keys($data));
    }

    public function removeLabel(string $label): Result
    {
        $data = $this->getCombinedData();

        unset($data[$label]);

        return $this->setData(array_values($data))->setLabels(array_keys($data));
    }

    public function getGrowthRate(): null|float|array
    {
        return $this->growthRate;
    }

    public function toResponse($request): Response
    {
        return new Response(
            $this->getData()
        );
    }

    /**
     * @throws Exception
     */
    public function offsetSet($offset, $value): void
    {
        throw new Exception('Result is immutable');
    }

    public function offsetExists($offset): bool
    {
        return isset($this->container[$offset]);
    }

    /**
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('Result is immutable');
    }

    public function offsetGet($offset): mixed
    {
        return $this->container[$offset] ?? null;
    }
}
