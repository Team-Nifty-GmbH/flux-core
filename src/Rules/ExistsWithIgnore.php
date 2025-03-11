<?php

namespace FluxErp\Rules;

use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\DatabaseRule;

class ExistsWithIgnore implements Rule
{
    use DatabaseRule;

    /**
     * The ID that should be ignored.
     */
    private mixed $ignore = null;

    public function getColumn(): string
    {
        return $this->column;
    }

    public function getTable(): Model|string
    {
        return $this->table;
    }

    /**
     * Ignore the given ID during the exists check.
     *
     * @return $this
     */
    public function ignore(mixed $id): static
    {
        if ($id instanceof Model) {
            return $this->ignoreModel($id);
        }

        $this->ignore = $id;

        return $this;
    }

    /**
     * Ignore the given model during the exists check.
     *
     * @return $this
     */
    public function ignoreModel(Model $model): static
    {
        $this->ignore = $model->{$model->getKeyName()};

        return $this;
    }

    public function message(): string
    {
        return __('validation.exists');
    }

    public function passes($attribute, $value): bool
    {
        $attribute = str_contains($attribute, '.') ? pathinfo($attribute, PATHINFO_EXTENSION) : $attribute;

        $query = DB::table($this->table)
            ->where($this->column !== 'NULL' ? $this->column : $attribute, $value);

        if ($this->ignore !== $value) {
            $query = $this->addConditions($query, $this->wheres);
        }

        return $query->exists();
    }

    /**
     * Add the given conditions to the query.
     */
    protected function addConditions(Builder $query, array $conditions): Builder
    {
        foreach ($conditions as $key => $value) {
            if ($value instanceof Closure) {
                $query->where(function ($query) use ($value): void {
                    $value($query);
                });
            } elseif (is_array($value)) {
                $this->addWhere($query, $value['column'], $value['value']);
            } else {
                $this->addWhere($query, $key, $value);
            }
        }

        return $query;
    }

    /**
     * Add a "where" clause to the given query.
     */
    protected function addWhere(Builder $query, string $key, string $extraValue): void
    {
        if ($extraValue === 'NULL') {
            $query->whereNull($key);
        } elseif ($extraValue === 'NOT_NULL') {
            $query->whereNotNull($key);
        } elseif (str_starts_with($extraValue, '!')) {
            $query->where($key, '!=', mb_substr($extraValue, 1));
        } else {
            $query->where($key, $extraValue);
        }
    }
}
