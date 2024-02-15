<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Project;
use FluxErp\Rules\ModelExists;

class FinishProjectRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(Project::class),
            ],
            'finish' => 'required|boolean',
        ];
    }
}
