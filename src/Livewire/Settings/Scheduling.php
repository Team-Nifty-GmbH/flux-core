<?php

namespace FluxErp\Livewire\Settings;

use FluxErp\Actions\Schedule\CreateSchedule;
use FluxErp\Actions\Schedule\DeleteSchedule;
use FluxErp\Actions\Schedule\UpdateSchedule;
use FluxErp\Enums\FrequenciesEnum;
use FluxErp\Facades\Repeatable;
use FluxErp\Livewire\DataTables\ScheduleList;
use FluxErp\Livewire\Forms\ScheduleForm;
use FluxErp\Models\Schedule;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;
use WireUi\Traits\Actions;

class Scheduling extends ScheduleList
{
    use Actions;

    public ?string $includeBefore = 'flux::livewire.settings.scheduling';

    public ScheduleForm $schedule;

    public array $repeatable;

    public function mount(): void
    {
        parent::mount();

        $this->headline = __('Schedules');

        $this->repeatable = Arr::mapWithKeys(
            Repeatable::all()->toArray(),
            fn (array $item, $key) => [
                $key => [
                    'id' => $key,
                    'name' => $item['name'],
                    'description' => __($item['description']),
                    'parameters' => $item['parameters'],
                ],
            ]
        );
    }

    protected function getTableActions(): array
    {
        return [
            DataTableButton::make()
                ->label(__('New'))
                ->icon('plus')
                ->color('primary')
                ->when(resolve_static(CreateSchedule::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit',
                ]),
        ];
    }

    protected function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            [
                'basic' => array_map(
                    fn ($item) => ['name' => $item, 'label' => __(Str::headline($item))],
                    FrequenciesEnum::getBasicFrequencies()
                ),
                'dayConstraints' => array_map(
                    fn ($item) => ['name' => $item, 'label' => __(Str::headline($item))],
                    FrequenciesEnum::getDayConstraints()
                ),
                'timeConstraints' => array_map(
                    fn ($item) => ['name' => $item, 'label' => __(Str::headline($item))],
                    FrequenciesEnum::getTimeConstraints()
                ),
            ]
        );
    }

    protected function getRowActions(): array
    {
        return [
            DataTableButton::make()
                ->icon('pencil')
                ->color('primary')
                ->when(resolve_static(UpdateSchedule::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
        ];
    }

    #[Renderless]
    public function edit(Schedule $schedule): void
    {
        $this->schedule->reset();
        $this->schedule->fill($schedule);

        $this->js(<<<'JS'
            $openModal('edit-schedule');
        JS);
    }

    #[Renderless]
    public function save(): bool
    {
        try {
            $this->schedule->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function delete(): bool
    {
        try {
            DeleteSchedule::make($this->schedule->toArray())
                ->checkPermission()
                ->validate()
                ->execute();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->loadData();

        return true;
    }

    #[Renderless]
    public function updatedScheduleName(): void
    {
        $this->schedule->description = $this->repeatable[$this->schedule->name]['description'];
        $this->schedule->parameters = $this->repeatable[$this->schedule->name]['parameters'];
    }
}
