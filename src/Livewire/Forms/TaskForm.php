<?php

namespace FluxErp\Livewire\Forms;

use Carbon\Carbon;
use FluxErp\Actions\FluxAction;
use FluxErp\Actions\Task\CreateTask;
use FluxErp\Actions\Task\DeleteTask;
use FluxErp\Actions\Task\UpdateTask;
use FluxErp\Models\Task;
use FluxErp\Traits\Livewire\SupportsAutoRender;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;

class TaskForm extends FluxForm
{
    use SupportsAutoRender;

    public array $additionalColumns = [];

    public ?string $budget = null;

    public array $categories = [];

    public ?string $description = null;

    public ?string $due_date = null;

    #[Locked]
    public ?int $id = null;

    public ?int $model_id = null;

    public ?string $model_type = null;

    #[Locked]
    public ?string $modelLabel = null;

    #[Locked]
    public ?string $modelUrl = null;

    public ?string $name = null;

    public ?int $priority = 0;

    public ?int $project_id = null;

    public ?int $responsible_user_id = null;

    public ?string $start_date = null;

    public string $state = 'open';

    public array $tags = [];

    public ?string $time_budget = null;

    public array $users = [];

    public function fill($values): void
    {
        if ($values instanceof Task) {
            $values->loadMissing(['tags:id', 'categories:id', 'users:id']);

            $values = $values->toArray();
            $values['tags'] = array_column($values['tags'] ?? [], 'id');
            $values['categories'] = array_column($values['categories'] ?? [], 'id');
            $values['users'] = array_column($values['users'] ?? [], 'id');
        }

        $values['start_date'] = ! is_null($values['start_date'] ?? null) ?
            Carbon::parse($values['start_date'])->toDateString() : null;
        $values['due_date'] = ! is_null($values['due_date'] ?? null) ?
            Carbon::parse($values['due_date'])->toDateString() : null;

        parent::fill($values);
    }

    public function reset(...$properties): void
    {
        parent::reset(...$properties);

        $this->responsible_user_id ??= auth()?->id();
        $this->users = $this->users ?: array_filter([auth()?->id()]);
    }

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
            $data = $this->toActionData();
            $data = array_merge(Arr::pull($data, 'additionalColumns', []), $data);
        }

        return parent::makeAction($name, $data);
    }
}
