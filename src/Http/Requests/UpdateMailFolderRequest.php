<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\MailFolder;
use FluxErp\Rules\ModelExists;

class UpdateMailFolderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'integer',
                new ModelExists(MailFolder::class),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                new ModelExists(MailFolder::class),
            ],
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'sometimes|required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
