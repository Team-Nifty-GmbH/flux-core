<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Livewire\Forms\TaskForm;
use FluxErp\Models\Task;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;
use WireUi\Traits\Actions;

class CreateTaskModal extends Component
{
    use Actions;

    public ?string $modelType;

    public ?int $modelId;

    public TaskForm $task;

    public array $availableStates = [];

    public function mount(): void
    {
        $this->task->additionalColumns = array_fill_keys(
            resolve_static(Task::class, 'additionalColumnsQuery')->pluck('name')?->toArray() ?? [],
            null
        );

        $this->availableStates = app(Task::class)
            ->getStatesFor('state')
            ->map(function ($state) {
                return [
                    'label' => __(ucfirst(str_replace('_', ' ', $state))),
                    'name' => $state,
                ];
            })
            ->toArray();
    }

    public function save(): bool
    {
        try {
            $this->task->model_type = morph_alias(morphed_model($this->modelType) ?? $this->modelType);
            $this->task->model_id = $this->modelId;
            $this->task->save();
        } catch (ValidationException|UnauthorizedException $e) {
            exception_to_notifications($e, $this);

            return false;
        }

        $this->task->reset();

        return true;
    }

    public function render(): View|Factory|Application
    {
        return view('flux::livewire.features.create-task-modal');
    }

    #[Renderless]
    public function resetTask(): void
    {
        $this->task->reset();

        $this->task->additionalColumns = array_fill_keys(
            resolve_static(Task::class, 'additionalColumnsQuery')->pluck('name')?->toArray() ?? [],
            null
        );
    }
}
