<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateLockedWorkTime;
use FluxErp\Livewire\Forms\LockedWorkTimeForm;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Traits\Trackable;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\ModelInfo\ModelInfo;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\DataTable;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use TeamNiftyGmbH\DataTable\Traits\HasEloquentListeners;

class WorkTimeList extends DataTable
{
    use HasEloquentListeners;

    protected string $model = WorkTime::class;

    protected string $view = 'flux::livewire.work-time.work-time-list';

    public LockedWorkTimeForm $workTime;

    public array $enabledCols = [
        'user.name',
        'name',
        'total_time_ms',
        'paused_time_ms',
        'started_at',
        'ended_at',
        'is_locked',
        'is_daily_work_time',
    ];

    public array $formatters = [
        'total_time_ms' => 'time',
        'paused_time_ms' => 'time',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public bool $isSelectable = true;

    public function itemToArray($item): array
    {
        $item = parent::itemToArray($item);
        $item['name'] = __($item['name']);

        return $item;
    }

    public function getAggregatable(): array
    {
        return array_merge(parent::getAggregatable(), ['paused_time_ms', 'total_time_ms']);
    }

    public function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('Edit'))
                ->icon('pencil')
                ->color('primary')
                ->wireClick('edit(record.id)')
                ->when(UpdateLockedWorkTime::canPerformAction(false)),
            DataTableButton::make()
                ->label(__('Delete'))
                ->icon('trash')
                ->color('negative')
                ->when(DeleteWorkTime::canPerformAction(false))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:confirm.icon.error' => __('wire:confirm.delete', ['model' => __('Work Time')]),
                ]),
        ];
    }

    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'workTimeTypes' => WorkTimeType::query()
                    ->get(['id', 'name'])
                    ->toArray(),
                'trackableTypes' => model_info_all()
                    ->filter(
                        fn (ModelInfo $modelInfo) => in_array(Trackable::class, $modelInfo->traits->toArray())
                    )
                    ->map(fn (ModelInfo $modelInfo) => $modelInfo->class)
                    ->toArray(),
            ]
        );
    }

    #[Renderless]
    public function edit(WorkTime $workTime): void
    {
        $this->workTime->reset();
        $this->workTime->fill($workTime);

        $this->js(<<<'JS'
            $openModal('edit-work-time');
        JS);
    }

    #[Renderless]
    public function delete(WorkTime $workTime): void
    {
        $this->workTime->reset();
        $this->workTime->fill($workTime);

        try {
            $this->workTime->delete();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->loadData();
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->workTime->save();
        } catch (UnauthorizedException|ValidationException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }
}
