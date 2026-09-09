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
        $model = $this->fresh
            ? $this->model->withoutRelations()->refresh()
            : $this->model;

        if ($this->with) {
            $model->loadMissing($this->with);
        }

        return Arr::sortByPattern(
            Arr::except(
                $model->toArray(),
                $this->except
            ),
            config('scout.sorted_searchable_keys.' . get_class($this->model), []),
        );
    }

    public function with(array|string $with): static
    {
        $this->with = $with;

        return $this;
    }

    public function withoutFresh(bool $withoutFresh = true): static
    {
        $this->fresh = ! $withoutFresh;

        return $this;
    }
}
