<?php

namespace FluxErp\Livewire\Forms;

use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\UpdateTask;
use Illuminate\Support\Arr;
use Livewire\Form;

class TaskForm extends Form
{
    public ?int $id = null;

    public ?int $project_id = null;

    public ?int $responsible_user_id = null;

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

    public function save(): void
    {
        if (! is_null($this->time_budget) && preg_match('/[0-9]*/', $this->time_budget)) {
            $this->time_budget = $this->time_budget . ':00';
        }

        $data = $this->toArray();
        $data = array_merge(Arr::pull($data, 'additionalColumns', []), $data);

        $action = $this->id ? UpdateTask::make($data) : CreateTask::make($data);

        $response = $action->validate()->execute();

        $this->fill($response);
    }
}
