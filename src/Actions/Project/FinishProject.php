<?php

namespace FluxErp\Actions\Project;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\FinishProjectRequest;
use FluxErp\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class FinishProject implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new FinishProjectRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'project.finish';
    }

    public static function description(): string|null
    {
        return 'finish project';
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function execute(): Model
    {
        $project = Project::query()
            ->whereKey($this->data['id'])
            ->first();

        $project->is_done = $this->data['finish'];
        $project->save();

        return $project;
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
