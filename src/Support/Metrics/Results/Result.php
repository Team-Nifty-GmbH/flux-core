<?php

namespace FluxErp\Support\Metrics\Results;

class Result
{
    public function __construct(
        protected array $data,
        protected array $labels,
        protected null|float|string|array $growthRate
    ) {}

    public static function make(array $data, array $labels, null|float|string|array $growthRate): static
    {
        return app(static::class, [
            'data' => $data,
            'labels' => $labels,
            'growthRate' => $growthRate,
        ]);
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

    public function mergeLabels(array $labels, float|int|string $default = 0): static
    {
        $data = $this->getCombinedData();

        foreach ($labels as $label) {
            $data[$label] ??= $default;
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
}
