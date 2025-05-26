<?php

namespace FluxErp\Livewire;

use Closure;
use FluxErp\Actions\Record\MergeRecords;
use FluxErp\Livewire\Forms\MergeRecordsForm;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\ModelInfo\Relations\RelationFinder;
use Spatie\ModelStates\State;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RecordMerging extends Component
{
    use Actions;

    #[Locked]
    public array $columns = [];

    #[Locked]
    public array $mainRecord = [];

    public MergeRecordsForm $mergeRecords;

    #[Locked]
    public array $records = [];

    public function render(): View
    {
        return view('flux::livewire.record-merging');
    }

    #[Renderless]
    public function clear(): void
    {
        $this->mergeRecords->reset();

        $this->columns = [];
        $this->records = [];
        $this->mainRecord = [];
    }

    #[Renderless]
    public function merge(): bool
    {
        try {
            $mainRecordIndex = $this->findKey(
                data_get($this->mergeRecords, 'merge_records'),
                fn ($item) => data_get($item, 'id') === data_get($this->mergeRecords, 'main_record.id')
            );

            $data = $this->mergeRecords->toArray();
            if ($mainRecordIndex !== false) {
                unset($data['merge_records'][$mainRecordIndex]);
            }

            MergeRecords::make($data)
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->notification()
            ->success(__('Records merged successfully'))
            ->send();

        $this->dispatch('loadData');

        return true;
    }

    #[Renderless]
    #[On('show-record-merging')]
    public function showRecordMerging(array $recordIds, string $modelClass): void
    {
        $this->mergeRecords->model_type = morph_alias($modelClass);

        $records = resolve_static($modelClass, 'query')
            ->whereKey($recordIds)
            ->get();

        $casts = [];
        $belongsTos = [];

        if ($records->isNotEmpty()) {
            $this->columns = array_map(
                fn ($column) => [
                    'name' => $column,
                    'label' => __(
                        Str::headline(
                            str_ends_with($column, '_id') ? Str::beforeLast($column, '_id') : $column
                        )
                    ),
                ],
                array_values(array_filter(
                    Schema::getColumnListing($records->first()->getTable()),
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
                ))
            );

            $casts = array_filter(
                $records->first()->getCasts(),
                fn ($cast) => is_a($cast, State::class, true)
            );

            $belongsTos = RelationFinder::forModel(resolve_static($modelClass, 'class'))
                ->filter(fn ($relation) => $relation->type === BelongsTo::class);
        }

        $this->records = $records->map(function ($record) use ($casts, $belongsTos) {
            $item = $record->toArray();

            foreach ($casts as $column => $cast) {
                if (is_a($cast, State::class, true)) {
                    data_set($item, $column, Str::headline(__(data_get($item, $column) ?? '')));
                }
            }

            foreach ($belongsTos as $belongsTo) {
                /** @var BelongsTo $relation */
                $relation = $record->{$belongsTo->name}();
                $related = $relation->first();
                if (is_null($related)) {
                    continue;
                }

                if (method_exists($related, 'getLabel')) {
                    data_set(
                        $item,
                        $relation->getForeignKeyName(),
                        $related->getLabel() ?? $related->getKey()
                    );
                } else {
                    data_set(
                        $item,
                        $relation->getForeignKeyName(),
                        $related->name ?? $related->getKey()
                    );
                }
            }

            return $item;
        })
            ->toArray();

        $this->mergeRecords->merge_records = array_map(
            fn ($record) => [
                'id' => data_get($record, 'id'),
                'columns' => [],
            ],
            $this->records
        );

        $this->js(
            <<<'JS'
                $modalOpen('merge-records-modal');
            JS
        );
    }

    #[Renderless]
    public function toggleColumn(int $recordId, string $column): void
    {
        $index = $this->findKey(
            $this->mergeRecords->merge_records,
            fn ($item) => data_get($item, 'id') === $recordId
        );

        if ($index === false) {
            return;
        }

        $columns = data_get($this->mergeRecords, 'merge_records.' . $index . '.columns') ?? [];

        if (in_array($column, $columns)) {
            $this->mergeRecords->merge_records[$index]['columns'] = array_diff($columns, [$column]);
        } else {
            $this->mergeRecords->merge_records[$index]['columns'] = array_merge($columns, [$column]);
        }

        if (data_get($this->mergeRecords, 'main_record.id') === $recordId) {
            $this->mergeRecords->main_record = data_get($this->mergeRecords->merge_records, $index) ?? [
                'id' => null,
                'columns' => [],
            ];
        }
    }

    #[Renderless]
    public function toggleRecord(int $recordId): void
    {
        if (data_get($this->mergeRecords, 'main_record.id') === $recordId) {
            $this->mergeRecords->main_record = [
                'id' => null,
                'columns' => [],
            ];

            $this->mainRecord = [];
        } else {
            $this->mergeRecords->main_record = [
                'id' => $recordId,
                'columns' => [],
            ];

            $index = $this->findKey(
                $this->mergeRecords->merge_records,
                fn ($item) => data_get($item, 'id') === $recordId
            );

            if ($index !== false) {
                $this->mainRecord = data_get($this->records, $index) ?? [];
            }
        }
    }

    protected function findKey(array $array, Closure $callback): false|int|string
    {
        foreach ($array as $key => $value) {
            if ($callback($value)) {
                return $key;
            }
        }

        return false;
    }
}
