<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Task;
use FluxErp\Rules\ExistsWithIgnore;
use FluxErp\States\Task\TaskState;
use Spatie\ModelStates\Validation\ValidStateRule;

class UpdateTaskRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new Task())->hasAdditionalColumnsValidationRules(),
            [
                'id' => 'required|integer|exists:tasks,id,deleted_at,NULL',
                'project_id' => [
                    'integer',
                    (new ExistsWithIgnore('projects', 'id'))->whereNull('deleted_at'),
                ],
                'address_id' => [
                    'integer',
                    (new ExistsWithIgnore('addresses', 'id'))->whereNull('deleted_at'),
                ],
                'user_id' => [
                    'integer',
                    (new ExistsWithIgnore('users', 'id'))->whereNull('deleted_at'),
                ],
                'name' => 'sometimes|string',
                'state' => [
                    'string',
                    ValidStateRule::make(TaskState::class),
                ],
                'is_done' => 'sometimes|boolean',
                'categories' => 'prohibits:category_id|required_without:category_id|array',
                'category_id' => 'prohibits:categories|required_without:categories|integer|exists:categories,id,model_type,'
                    . Task::class,
            ],
        );
    }
}
