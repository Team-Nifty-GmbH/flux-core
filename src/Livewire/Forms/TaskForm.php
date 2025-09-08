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

    public ?string $due_time = null;

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

    public ?string $start_time = null;

    public string $state = 'open';

    public array $tags = [];

    public ?string $time_budget = null;

    public array $users = [];

    public function fill($values): void
    {
        if ($values instanceof Task) {
            $timezone = auth()->user()->timezone ?? config('app.timezone', 'UTC');

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

        if (! empty($values['start_time'])) {
            $values['start_time'] = Carbon::createFromFormat('H:i:s', $values['start_time'], $timezone)
                ->setTimezone($timezone)
                ->format('H:i');
        } else {
            $values['start_time'] = null;
        }

        if (! empty($values['due_time'])) {
            $values['due_time'] = Carbon::createFromFormat('H:i:s', $values['due_time'], $timezone)
                ->setTimezone($timezone)
                ->format('H:i');
        } else {
            $values['due_time'] = null;
        }

        parent::fill($values);
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
        $timezone = auth()->user()->timezone ?? config('app.timezone', 'UTC');

        if (! is_null($this->time_budget) && preg_match('/[0-9]*/', $this->time_budget)) {
            $this->time_budget = $this->time_budget . ':00';
        }

        if (! is_null($this->start_time)) {
            $this->start_time = Carbon::createFromFormat('H:i', $this->start_time, $timezone)
                ->utc()
                ->format('H:i');
        }

        if (! is_null($this->due_time)) {
            $this->due_time = Carbon::createFromFormat('H:i', $this->due_time, $timezone)
                ->utc()
                ->format('H:i');
        }

        if (is_null($data)) {
            $data = $this->toArray();
            $data = array_merge(Arr::pull($data, 'additionalColumns', []), $data);
        }

        return parent::makeAction($name, $data);
    }
}
