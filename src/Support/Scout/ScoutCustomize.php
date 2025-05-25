<?php

namespace FluxErp\Support\Scout;

use FluxErp\Traits\Makeable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class ScoutCustomize
{
    use Makeable;

    protected array|float|int|string $except = [];

    protected bool $fresh = true;

    protected array|string $with = [];

    public function __construct(protected Model $model) {}

    public function except(array|float|int|string $except): static
    {
        $this->except = $except;

        return $this;
    }

    public function toSearchableArray(): array
    {
        if ($this->fresh) {
            $this->model->fresh($this->with);
        }

        if ($this->with) {
            $this->model->loadMissing($this->with);
        }

        return Arr::sortByPattern(
            Arr::except(
                $this->model->toArray(),
                $this->except
            ),
            config('scout.sorted_searchable_keys.' . $this->model::class, []),
        );
    }

    public function with(array|string $with): static
    {
        $this->with = $with;

        return $this;
    }

    public function withoutFresh(bool $fresh = false): static
    {
        $this->fresh = $fresh;

        return $this;
    }
}
