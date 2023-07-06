<?php

namespace FluxErp\Actions\ProjectTask;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\FinishProjectTaskRequest;
use FluxErp\Models\ProjectTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class FinishProjectTask implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new FinishProjectTaskRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'project-task.finish';
    }

    public static function description(): string|null
    {
        return 'finish project task';
    }

    public static function models(): array
    {
        return [ProjectTask::class];
    }

    public function execute(): Model
    {
        $task = ProjectTask::query()
            ->whereKey($this->data['id'])
            ->first();

        $task->is_done = $this->data['finish'];
        $task->save();

        return $task;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
