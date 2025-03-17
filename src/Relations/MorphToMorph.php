<?php

namespace FluxErp\Relations;

use FluxErp\Traits\HasRelatedModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Arr;

class MorphToMorph extends MorphToMany
{
    protected string $relatedMorphClass;

    protected string $relatedMorphType;

    public function __construct(
        Builder $query, Model $parent, string $name, string $relatedMorph, string $table, string $foreignPivotKey,
        string $relatedPivotKey, string $parentKey, string $relatedKey, $relationName = null, $inverse = false)
    {
        $this->relatedMorphType = $relatedMorph . '_type';
        $this->relatedMorphClass = $inverse ? $parent->getMorphClass() : $query->getModel()->getMorphClass();

        parent::__construct(
            $query, $parent, $name, $table, $foreignPivotKey,
            $relatedPivotKey, $parentKey, $relatedKey, $relationName, $inverse
        );
    }

    public function addEagerConstraints(array $models): void
    {
        parent::addEagerConstraints($models);

        $this->query->where($this->qualifyPivotColumn($this->relatedMorphType), $this->relatedMorphClass);
    }

    public function attach($id, array $attributes = [], $touch = true): void
    {
        parent::attach($id, $attributes, $touch);

        $this->parent->fireModelEvent('relatedModelsChanged');

        if (in_array(HasRelatedModel::class, class_uses_recursive($this->related))) {
            $this->related->fireModelEvent('relatedModelsChanged');
        }
    }

    public function detach($ids = null, $touch = true): int
    {
        $detached = parent::detach($ids, $touch);

        if ($detached) {
            $this->parent->fireModelEvent('relatedModelsChanged');

            if (in_array(HasRelatedModel::class, class_uses_recursive($this->related))) {
                $this->related->fireModelEvent('relatedModelsChanged');
            }
        }

        return $detached;
    }

    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*']): Builder
    {
        return parent::getRelationExistenceQuery($query, $parentQuery, $columns)
            ->where($this->qualifyPivotColumn($this->morphType), $this->morphClass)
            ->where($this->qualifyPivotColumn($this->relatedMorphType), $this->relatedMorphClass);
    }

    public function newPivotQuery(): \Illuminate\Database\Query\Builder
    {
        return parent::newPivotQuery()
            ->where($this->morphType, $this->morphClass)
            ->where($this->relatedMorphType, $this->relatedMorphClass);
    }

    public function sync($ids, $detaching = true): array
    {
        $changes = parent::sync($ids, $detaching);

        if (array_filter($changes)) {
            $this->parent->fireModelEvent('relatedModelsChanged');

            if (in_array(HasRelatedModel::class, class_uses_recursive($this->related))) {
                $this->related->fireModelEvent('relatedModelsChanged');
            }
        }

        return $changes;
    }

    protected function addWhereConstraints(): MorphToMorph
    {
        parent::addWhereConstraints();

        $this->query->where($this->qualifyPivotColumn($this->relatedMorphType), $this->relatedMorphClass);

        return $this;
    }

    protected function baseAttachRecord($id, $timed): array
    {
        return Arr::add(
            parent::baseAttachRecord($id, $timed), $this->relatedMorphType, $this->relatedMorphClass
        );
    }
}
