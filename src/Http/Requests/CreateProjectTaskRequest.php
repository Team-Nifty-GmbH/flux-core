<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\ProjectTask;
use FluxErp\States\ProjectTask\ProjectTaskState;
use Spatie\ModelStates\Validation\ValidStateRule;

class CreateProjectTaskRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge(
            (new ProjectTask())->hasAdditionalColumnsValidationRules(),
            [
                'project_id' => 'required|integer|exists:projects,id,deleted_at,NULL',
                'categories' => 'prohibits:category_id|required_without:category_id|array',
                'category_id' => 'prohibits:categories|required_without:categories|integer|exists:categories,id,model_type,'
                    . ProjectTask::class,
                'address_id' => 'required|integer|exists:addresses,id,deleted_at,NULL',
                'user_id' => 'required|integer|exists:users,id,deleted_at,NULL',
                'name' => 'required|string',
                'state' => [
                    'string',
                    ValidStateRule::make(ProjectTaskState::class),
                ],
            ]
        );
    }
}
