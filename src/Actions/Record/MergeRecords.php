<?php

namespace FluxErp\Actions\Record;

use FluxErp\Actions\FluxAction;
use FluxErp\Rulesets\Record\MergeRecordsRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use ReflectionException;
use ReflectionMethod;
use Spatie\ModelInfo\ModelInfo;

class MergeRecords extends FluxAction
{
    public static function models(): array
    {
        return [];
    }

    protected function getRulesets(): string|array
    {
        return MergeRecordsRuleset::class;
    }

    public function performAction(): Model
    {
        $mainRecord = morph_to($this->getData('model_type'), $this->getData('main_record.id'));

        $columns = array_filter(
            Schema::getColumnListing($mainRecord->getTable()),
            fn ($column) => ! in_array(
                $column,
                [
                    'id',
                    'uuid',
                    'created_at',
                    'created_by',
                    'updated_at',
                    'updated_by',
                    'deleted_at',
                    'deleted_by',
                ]
            )
        );

        // Filter the columns so that no column is used multiple times
        $columns = array_diff($columns, $this->getData('main_record.columns') ?? []);

        foreach ($mergeRecords = $this->getData('merge_records') as $index => $mergeRecord) {
            if (blank($columns)) {
                $mergeRecords[$index]['columns'] = [];

                continue;
            }

            $mergeRecords[$index]['columns'] = array_intersect(
                $columns,
                data_get($mergeRecord, 'columns') ?? []
            );

            $columns = array_diff($columns, data_get($mergeRecords, $index . '.columns') ?? []);
        }

        // If a column is not given in any other record, use the value from the main record
        $this->data['main_record']['columns'] = array_merge(
            $this->getData('main_record.columns') ?? [],
            $columns
        );

        // Move related records to the main record depending on the respective relation
        $relations = data_get(ModelInfo::forModel($mainRecord), 'relations')
            ?->filter(function ($relation) {
                return in_array(
                    $relation->type,
                    [
                        BelongsToMany::class,
                        HasOne::class,
                        HasMany::class,
                        MorphOne::class,
                        MorphMany::class,
                        MorphToMany::class,
                    ]
                );
            });

        // Update the main record with the values from the merge records (if any)
        foreach ($mergeRecords as $mergeRecord) {
            if (blank(data_get($mergeRecord, 'columns'))) {
                continue;
            }

            $recordQuery = morph_to($this->getData('model_type'), $mergeRecord['id'], true);
            $values = $recordQuery
                ->select(data_get($mergeRecord, 'columns'))
                ->first()
                ?->toArray();

            $mainRecord->fill($values);
        }

        $mainRecord->save();

        foreach ($relations as $relationItem) {
            try {
                $method = new ReflectionMethod($mainRecord, $relationItem->name);
                $relation = $method->invoke($mainRecord);
            } catch (ReflectionException) {
                continue;
            }

            // On MorphOne and HasOne relations if it is not from the main record,
            // it must be updated accordingly, and the related record from the main record must be deleted.
            // For now, related records from these relations are not replaced to avoid data loss.
            // Update each related model separately to trigger the model events.
            switch (true) {
                case $relation instanceof HasOne:
                case $relation instanceof HasMany:
                case $relation instanceof MorphOne:
                case $relation instanceof MorphMany:
                    $relation->getRelated()
                        ->newQuery()
                        ->when(
                            $relation instanceof MorphOne || $relation instanceof MorphMany,
                            fn ($query) => $query->where(
                                $relation->getQualifiedMorphType(),
                                $mainRecord->getMorphClass()
                            )
                        )
                        ->whereIn($relation->getQualifiedForeignKeyName(), $this->getData('merge_records.*.id'))
                        ->get()
                        ->each(fn (Model $model) => $model
                            ->fill([
                                $relation->getForeignKeyName() => $mainRecord->getKey(),
                            ])
                            ->save()
                        );
                    break;
                case $relation instanceof BelongsToMany:
                    // If the relation has pivot columns, we assume that duplicate entries are allowed
                    $pivotColumns = $relation->getPivotColumns();
                    $columns = array_merge(
                        [
                            $relation->getForeignPivotKeyName(),
                            $relation->getRelatedPivotKeyName(),
                        ],
                        $relation instanceof MorphToMany ? [$relation->getMorphType()] : [],
                        $pivotColumns
                    );

                    $existingRelatedIds = $relation->newPivotStatement()
                        ->where($relation->getQualifiedForeignPivotKeyName(), $mainRecord->getKey())
                        ->when(
                            $relation instanceof MorphToMany && $relation->getInverse() === false,
                            fn (Builder $query) => $query->where(
                                $relation->getQualifiedMorphTypeName(),
                                $mainRecord->getMorphClass()
                            )
                        )
                        ->pluck($relation->getQualifiedRelatedPivotKeyName())
                        ->unique()
                        ->toArray();

                    $relation->newPivotStatement()
                        ->insertUsing(
                            $columns,
                            $relation->newPivotStatement()
                                ->select(array_merge(
                                    [DB::raw($mainRecord->getKey())],
                                    array_diff($columns, [$relation->getForeignPivotKeyName()])
                                ))
                                ->when(
                                    $relation instanceof MorphToMany && $relation->getInverse() === false,
                                    fn ($query) => $query->where(
                                        $relation->getQualifiedMorphTypeName(),
                                        $mainRecord->getMorphClass()
                                    )
                                )
                                ->whereIn(
                                    $relation->getQualifiedForeignPivotKeyName(),
                                    $this->getData('merge_records.*.id')
                                )
                                ->when(
                                    ! $pivotColumns,
                                    fn (Builder $query) => $query
                                        ->whereNotIn(
                                            $relation->getQualifiedRelatedPivotKeyName(),
                                            $existingRelatedIds
                                        )
                                        ->groupBy(array_merge(
                                            [$relation->getRelatedPivotKeyName()],
                                            $relation instanceof MorphToMany ? [$relation->getMorphType()] : [],
                                        ))
                                )
                        );
                    break;
            }
        }

        // Delete the merge records
        $model = morphed_model($this->getData('model_type'));
        resolve_static($model, 'query')
            ->whereKey($this->getData('merge_records.*.id'))
            ->get()
            ->each(fn (Model $model) => $model->delete());

        return $mainRecord->withoutRelations()->refresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        // Validate duplicate columns over all records (main record and merge records)
        $columns = array_merge(
            $this->getData('main_record.columns') ?? [],
            Arr::flatten($this->getData('merge_records.*.columns') ?? [])
        );

        if (count($columns) !== count(array_unique($columns))) {
            throw ValidationException::withMessages([
                'columns' => ['The given columns must be unique.'],
            ]);
        }

        // Validate that the main record is not in the merge records
        if (in_array(
            $this->getData('main_record.id'),
            Arr::flatten($this->getData('merge_records.*.id'))
        )) {
            throw ValidationException::withMessages([
                'main_record' => [
                    'The main record must not be in the merge records.',
                ],
            ]);
        }

        // Validate morphTo relations to ensure that the foreign key and morph type are from the same record
        $model = morphed_model($this->getData('model_type'));

        $morphTos = data_get(ModelInfo::forModel($model), 'relations')
            ?->filter(function ($relation) {
                return $relation->type === MorphTo::class;
            });

        $keyedColumns = array_filter(
            Arr::dot($this->getData()),
            fn ($item) => str_contains($item, 'columns'),
            ARRAY_FILTER_USE_KEY
        );

        foreach ($morphTos as $morphTo) {
            try {
                $method = new ReflectionMethod($model, $morphTo->name);
                $relation = $method->invoke($model);
            } catch (ReflectionException) {
                continue;
            }

            $foreignKey = array_search(
                $relation->getForeignKeyName(),
                $keyedColumns
            );
            $morphType = array_search(
                $relation->getMorphType(),
                $keyedColumns
            );

            if (
                ($foreignKey !== false xor $morphType !== false)
                || (Str::beforeLast((string) $foreignKey, '.')
                    !== Str::beforeLast((string) $morphType, '.'))
            ) {
                throw ValidationException::withMessages([
                    'columns' => [
                        __(
                            '\':foreignKey\' and \':morphType\' must be from the same record.',
                            [
                                'foreignKey' => $relation->getForeignKeyName(),
                                'morphType' => $relation->getMorphType(),
                            ]
                        ),
                    ],
                ]);
            }
        }
    }
}
