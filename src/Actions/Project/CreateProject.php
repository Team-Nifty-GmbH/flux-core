<?php

namespace FluxErp\Actions\Project;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateProjectRequest;
use FluxErp\Models\Category;
use FluxErp\Models\Project;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateProject implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateProjectRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'project.create';
    }

    public static function description(): string|null
    {
        return 'create project';
    }

    public static function models(): array
    {
        return [Project::class];
    }

    public function execute(): Project
    {
        $project = new Project($this->data);
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

        if ($this->data['parent_id'] ?? false) {
            $parentProject = Project::query()
                ->whereKey($this->data['parent_id'])
                ->first();

            if (! $parentProject) {
                throw ValidationException::withMessages([
                    'parent_id' => [__('Parent project not found')],
                ])->errorBag('createProject');
            }
        }

        $intArray = array_filter($this->data['categories'], function ($value) {
            return is_int($value) && $value > 0;
        });

        $categories = Category::query()
            ->whereKey($this->data['category_id'])
            ->with('children:id,parent_id')
            ->first();
        $categories = array_column(to_flat_tree($categories->children->toArray()), 'id');

        $diff = array_diff($intArray, $categories);
        if (count($diff) > 0 || count($categories) === 0) {
            throw ValidationException::withMessages([
                'categories' => [
                    __(
                        'categories \':values\' not found',
                        ['values' => implode(', ', array_values($diff))]
                    )
                ],
            ])->errorBag('createProject');
        }

        return $this;
    }
}
