<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Task;
use FluxErp\Rules\ModelExists;

class FinishTaskRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Task::class),
            ],
            'finish' => 'required|boolean',
        ];
    }
}
