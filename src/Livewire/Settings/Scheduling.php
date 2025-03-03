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
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Spatie\Permission\Exceptions\UnauthorizedException;
use TeamNiftyGmbH\DataTable\Htmlables\DataTableButton;

class Scheduling extends ScheduleList
{
    use Actions;

    public ?string $includeBefore = 'flux::livewire.settings.scheduling';

    public ScheduleForm $schedule;

    public array $repeatable;

    public function mount(): void
    {
        parent::mount();

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
                ->text(__('New'))
                ->icon('plus')
                ->color('indigo')
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
                    fn ($item) => ['value' => $item, 'label' => __(Str::headline($item))],
                    FrequenciesEnum::getBasicFrequencies()
                ),
                'dayConstraints' => array_map(
                    fn ($item) => ['value' => $item, 'label' => __(Str::headline($item))],
                    FrequenciesEnum::getDayConstraints()
                ),
                'timeConstraints' => array_map(
                    fn ($item) => ['value' => $item, 'label' => __(Str::headline($item))],
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
                ->color('indigo')
                ->when(resolve_static(UpdateSchedule::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'edit(record.id)',
                ]),
            DataTableButton::make()
                ->icon('trash')
                ->color('red')
                ->when(resolve_static(DeleteSchedule::class, 'canPerformAction', [false]))
                ->attributes([
                    'wire:click' => 'delete(record.id)',
                    'wire:flux-confirm.type.error' => __('wire:confirm.delete', ['model' => __('Schedule')]),
                ]),
        ];
    }

    #[Renderless]
    public function edit(Schedule $schedule): void
    {
        $this->schedule->reset();
        $this->schedule->fill($schedule);

        $this->js(<<<'JS'
            $modalOpen('edit-schedule-modal');
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
    public function delete(Schedule $schedule): bool
    {
        $this->schedule->reset();
        $this->schedule->fill($schedule);

        try {
            $this->schedule->delete();
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
