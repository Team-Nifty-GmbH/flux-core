<?php

namespace FluxErp\Livewire\Forms;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Models\Task;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;

class TaskForm extends FluxForm
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

    public array $categories = [];

    public array $tags = [];

    protected function getActions(): array
    {
        return [
            'create' => CreateTask::class,
            'update' => UpdateTask::class,
            'delete' => DeleteTask::class,
        ];
    }

    protected function makeAction(string $name, ?array $data = null): FluxAction
    {
        if (! is_null($this->time_budget) && preg_match('/[0-9]*/', $this->time_budget)) {
            $this->time_budget = $this->time_budget . ':00';
        }

        if (is_null($data)) {
            $data = $this->toArray();
            $data = array_merge(Arr::pull($data, 'additionalColumns', []), $data);
        }

        return parent::makeAction($name, $data);
    }

    public function fill($values): void
    {
        if ($values instanceof Task) {
            $values->loadMissing(['tags:id', 'categories:id']);

            $values = $values->toArray();
            $values['tags'] = array_column($values['tags'] ?? [], 'id');
            $values['categories'] = array_column($values['categories'] ?? [], 'id');
        }

        $values['start_date'] = ! is_null($values['start_date'] ?? null) ?
            Carbon::parse($values['start_date'])->toDateString() : null;
        $values['due_date'] = ! is_null($values['due_date'] ?? null) ?
            Carbon::parse($values['due_date'])->toDateString() : null;

        parent::fill($values);
    }
}
