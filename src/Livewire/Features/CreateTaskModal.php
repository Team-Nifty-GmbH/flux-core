<?php

namespace FluxErp\Livewire\Features;

use FluxErp\Livewire\Forms\TaskForm;
use FluxErp\Models\Task;
use FluxErp\Traits\Livewire\Actions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CreateTaskModal extends Component
{
    use Actions;

    public ?string $modelType = null;

    public ?int $modelId = null;

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
                    'label' => __(Str::headline($state)),
                    'name' => $state,
                ];
            })
            ->toArray();
    }

    public function save(): bool
    {
        try {
            if (! is_null($this->modelType)) {
                $this->task->model_type = morph_alias(morphed_model($this->modelType) ?? $this->modelType);
                $this->task->model_id = $this->modelId;
            } else {
                $this->task->model_type = null;
                $this->task->model_id = null;
            }

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
