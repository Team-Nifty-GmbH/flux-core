<?php

namespace FluxErp\Livewire\Forms;

use Carbon\Carbon;
use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Models\Task;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Livewire\Form;

class TaskForm extends Form
{
    #[Locked]
    public ?int $id = null;

    public ?int $project_id = null;

    public ?int $responsible_user_id = null;

    public ?string $model_type = null;

    public ?int $model_id = null;

    public ?string $name = null;

    public ?string $description = null;

    public ?string $start_date = null;

    public ?string $due_date = null;

    public ?int $priority = 0;

    public string $state = 'open';

    public ?string $time_budget = null;

    public ?string $budget = null;

    public array $users = [];

    public array $additionalColumns = [];

    public array $tags = [];

    public function save(): void
    {
        if (! is_null($this->time_budget) && preg_match('/[0-9]*/', $this->time_budget)) {
            $this->time_budget = $this->time_budget.':00';
        }

        $data = $this->toArray();
        $data = array_merge(Arr::pull($data, 'additionalColumns', []), $data);

        $action = $this->id ? UpdateTask::make($data) : CreateTask::make($data);

        $response = $action->validate()->execute();

        $this->fill($response);
    }

    public function fill($values): void
    {
        if ($values instanceof Task) {
            $values->loadMissing(['tags:id']);

            $values = $values->toArray();
            $values['tags'] = array_column($values['tags'] ?? [], 'id');
        }

        $values['start_date'] = ! is_null($values['start_date'] ?? null) ?
            Carbon::parse($values['start_date'])->toDateString() : null;
        $values['due_date'] = ! is_null($values['due_date'] ?? null) ?
            Carbon::parse($values['due_date'])->toDateString() : null;

        parent::fill($values);
    }
}
