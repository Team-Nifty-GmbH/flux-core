<?php

namespace FluxErp\Livewire\DataTables;

use FluxErp\Actions\Order\CreateOrder;
use FluxErp\Actions\WorkTime\CreateLockedWorkTime;
use FluxErp\Actions\WorkTime\CreateOrdersFromWorkTimes;
use FluxErp\Actions\WorkTime\DeleteWorkTime;
use FluxErp\Actions\WorkTime\UpdateLockedWorkTime;
use FluxErp\Livewire\Forms\CreateOrdersFromWorkTimesForm;
use FluxErp\Livewire\Forms\LockedWorkTimeForm;
use FluxErp\Models\OrderType;
use FluxErp\Models\WorkTime;
use FluxErp\Models\WorkTimeType;
use FluxErp\Traits\Trackable;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\ModelInfo\ModelInfo;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class WorkTimeList extends BaseDataTable
{
    protected string $model = WorkTime::class;

    protected ?string $includeBefore = 'flux::livewire.work-time.work-time-list';

    public LockedWorkTimeForm $workTime;

    public CreateOrdersFromWorkTimesForm $createOrdersFromWorkTimes;

    public array $enabledCols = [
        'user.name',
        'name',
        'total_time_ms',
        'paused_time_ms',
        'started_at',
        'ended_at',
        'is_billable',
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

    #[Renderless]
    public function toggleIsBillable(bool $isBillable): void
    {
        foreach ($this->getSelectedModelsQuery()->pluck('id') as $id) {
            try {
                UpdateLockedWorkTime::make([
                    'id' => $id,
                    'is_billable' => $isBillable,
                ])
                    ->checkPermission()
                    ->validate()
                    ->execute();
            } catch (ValidationException|UnauthorizedException $e) {
                exception_to_notifications($e, $this);

                return;
            }
        }

        $this->reset('selected');
        $this->loadData();
    }

    #[Renderless]
    public function createOrders(): void
    {
        try {
            CreateOrdersFromWorkTimes::make(
                array_merge(
                    $this->createOrdersFromWorkTimes->toArray(),
                    ['work_times' => $this->getSelectedModelsQuery()->get('id')->toArray()]
                )
            )
                ->checkPermission()
                ->validate()
                ->executeAsync();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return;
        }

        $this->reset('selected');
        $this->loadData();
    }

    protected function itemToArray($item): array
    {
        $item = parent::itemToArray($item);
        $item['name'] = __($item['name']);

        return $item;
    }

    protected function getReturnKeys(): array
    {
        return array_merge(
            parent::getReturnKeys(),
            ['name']
        );
    }

    protected function getSelectedActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Create Orders'))
                ->color('indigo')
                ->xOnClick(<<<'JS'
                    $modalOpen('create-orders');
                JS)
                ->when(fn () => resolve_static(CreateOrder::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Change is billable'))
                ->xOnClick(<<<'JS'
                    $modalOpen('toggle-is-billable');
                JS)
                ->when(fn () => resolve_static(UpdateLockedWorkTime::class, 'canPerformAction', [false])),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('New'))
                ->color('indigo')
                ->icon('plus')
                ->wireClick('edit')
                ->when(resolve_static(CreateLockedWorkTime::class, 'canPerformAction', [false])),
        ];
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->text(__('Edit'))
                ->icon('pencil')
                ->color('indigo')
                ->wireClick('edit(record.id)')
                ->when(resolve_static(UpdateLockedWorkTime::class, 'canPerformAction', [false])),
            DataTableButton::make()
                ->text(__('Delete'))
                ->icon('trash')
                ->color('red')
                ->when(resolve_static(DeleteWorkTime::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Work Time')]),
                ]),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'workTimeTypes' => resolve_static(WorkTimeType::class, 'query')
                    ->get(['id', 'name', 'is_billable'])
                    ->toArray(),
                'trackableTypes' => model_info_all()
                    ->unique('morphClass')
                    ->filter(fn (ModelInfo $modelInfo) => in_array(
                        Trackable::class,
                        class_uses_recursive($modelInfo->class)
                    ))
                    ->map(fn ($modelInfo) => [
                        'label' => __(Str::headline($modelInfo->morphClass)),
                        'value' => $modelInfo->morphClass,
                    ])
                    ->toArray(),
                'orderTypes' => resolve_static(OrderType::class, 'query')
                    ->where('is_hidden', false)
                    ->where('is_active', true)
                    ->get(['id', 'name', 'order_type_enum'])
                    ->filter(fn (OrderType $orderType) => ! $orderType->order_type_enum->isPurchase())
                    ->pluck('name', 'id')
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
            $modalOpen('edit-work-time-modal');
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
