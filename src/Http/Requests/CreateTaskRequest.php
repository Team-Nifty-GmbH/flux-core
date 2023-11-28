<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Task;
use FluxErp\States\Task\TaskState;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateTaskRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return array_merge(
            (new Task())->hasAdditionalColumnsValidationRules(),
            [
                'uuid' => 'string|uuid|unique:tasks,uuid',
                'project_id' => 'required|integer|exists:projects,id,deleted_at,NULL',
                'categories' => 'prohibits:category_id|required_without:category_id|array',
                'category_id' => 'prohibits:categories|required_without:categories|integer|exists:categories,id,model_type,'
                    . Task::class,
                'address_id' => 'required|integer|exists:addresses,id,deleted_at,NULL',
                'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
                'name' => 'required|string',
                'state' => [
                    'string',
                    ValidStateRule::make(TaskState::class),
                ],
            ]
        );
    }
}
