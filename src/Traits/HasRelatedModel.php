<?php

namespace FluxErp\Traits;

use FluxErp\Relations\MorphToMorph;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

trait HasRelatedModel
{
    public static function bootHasRelatedModel(): void
    {
        self::relatedModelsChanged(function (Model $model) {
            Cache::putMany([
                $model->getMorphClass() . '.' . $model->getKey() . '.related.models' => DB::table('model_related')
                    ->where('model_type', $model->getMorphClass())
                    ->where('model_id', $model->getKey())
                    ->groupBy('related_type')
                    ->pluck('related_type')
                    ->toArray(),
                $model->getMorphClass() . '.' . $model->getKey() . '.related.by' => DB::table('model_related')
                    ->where('related_type', $model->getMorphClass())
                    ->where('related_id', $model->getKey())
                    ->groupBy('model_type')
                    ->pluck('model_type')
                    ->toArray(),
            ]);
        });
    }

    public static function relatedModelsChanged($callback): void
    {
        static::registerModelEvent('relatedModelsChanged', $callback);
    }

    public function relatedModel(string $model = null): MorphToMorph
    {
        if (is_null($model)) {
            $model = Cache::get($this->getMorphClass() . '.' . $this->getKey() . '.related.models');

            $model = $model ? array_shift($model) : $this->getMorphClass();
        }

        return $this->morphToMorph(
            related: $model,
            name: 'model',
            relatedMorph: 'related',
            table: 'model_related',
        );
    }

    public function relatedBy(string $model = null): MorphToMorph
    {
        if (is_null($model)) {
            $model = Cache::get($this->getMorphClass() . '.' . $this->getKey() . '.related.by');

            $model = $model ? array_shift($model) : $this->getMorphClass();
        }

        return $this->morphedByMorph(
            related: $model,
            name: 'model',
            relatedMorph: 'related',
            table: 'model_related'
        );
    }

    public function relatedModels(): Collection
    {
        $models = Cache::rememberForever(
            $this->getMorphClass() . '.' . $this->getKey() . '.related.models',
            fn () => DB::table('model_related')
                ->where('model_type', $this->getMorphClass())
                ->where('model_id', $this->getKey())
                ->groupBy('related_type')
                ->pluck('related_type')
                ->toArray()
        );

        $related = new Collection([]);
        foreach ($models as $model) {
            $related = $related->merge($this->relatedModel($model)->get());
        }

        return $related;
    }

    public function relatedByModels(): Collection
    {
        $relatedByModels = Cache::rememberForever(
            $this->getMorphClass() . '.' . $this->getKey() . '.related.by',
            fn () => DB::table('model_related')
                ->where('related_type', $this->getMorphClass())
                ->where('related_id', $this->getKey())
                ->groupBy('model_type')
                ->pluck('model_type')
                ->toArray()
        );

        $relatedBy = new Collection([]);
        foreach ($relatedByModels as $relatedByModel) {
            $relatedBy = $relatedBy->merge($this->relatedBy($relatedByModel)->get());
        }

        return $relatedBy;
    }

    /**
     * Get the observable event names.
     */
    public function getObservableEvents(): array
    {
        return array_merge(
            parent::getObservableEvents(),
            [
                'relatedModelsChanged',
            ],
            $this->observables
        );
    }

    public function fireModelEvent($event, $halt = true): mixed
    {
        if (! isset(static::$dispatcher)) {
            return true;
        }

        $method = $halt ? 'until' : 'dispatch';

        $result = $this->filterModelEventResults(
            $this->fireCustomModelEvent($event, $method)
        );

        if (false === $result) {
            return false;
        }

        $payload = [$this];

        return ! empty($result) ? $result : static::$dispatcher->{$method}(
            "eloquent.{$event}: " . static::class, $payload
        );
    }

    /**
     * Define a polymorphic many-to-many relationship with polymorphic related records.
     *
     * @return MorphToMorph
     */
    protected function morphToMorph(
        string $related, string $name, string $relatedMorph, string $table, string $foreignPivotKey = null,
        string $relatedPivotKey = null, string $parentKey = null, string $relatedKey = null,
        bool $inverse = false)
    {
        $caller = $this->guessBelongsToManyRelation();

        // First, we will need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we will make the query
        // instances, as well as the relationship instances we need for these.
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $name . '_id';

        $relatedPivotKey = $relatedPivotKey ?: $relatedMorph . '_id';

        // Now we're ready to create a new query builder for the related model and
        // the relationship instances for this relation. This relation will set
        // appropriate query constraints then entirely manage the hydrations.

        return $this->newMorphToMorph(
            $instance->newQuery(), $this, $name, $relatedMorph, $table,
            $foreignPivotKey, $relatedPivotKey, $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(), $caller, $inverse
        );
    }

    /**
     * Instantiate a new MorphToMorph relationship.
     *
     * @return MorphToMorph
     */
    protected function newMorphToMorph(
        Builder $query, Model $parent, string $name, string $related, string $table, string $foreignPivotKey,
        string $relatedPivotKey, string $parentKey, string $relatedKey, string $relationName = null,
        bool $inverse = false)
    {
        return new MorphToMorph(
            $query, $parent, $name, $related, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
            $relationName, $inverse
        );
    }

    /**
     * Define a polymorphic, inverse many-to-many relationship with polymorphic related records.
     *
     * @return MorphToMorph
     */
    protected function morphedByMorph(
        string $related, string $name, string $relatedMorph, string $table = null,
        string $foreignPivotKey = null, string $relatedPivotKey = null, string $parentKey = null,
        string $relatedKey = null)
    {
        $foreignPivotKey = $foreignPivotKey ?: $relatedMorph . '_id';

        $relatedPivotKey = $relatedPivotKey ?: $name . '_id';

        return $this->morphToMorph(
            $related, $name, $relatedMorph, $table, $foreignPivotKey,
            $relatedPivotKey, $parentKey, $relatedKey, true
        );
    }
}
