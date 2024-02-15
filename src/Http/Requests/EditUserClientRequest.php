<?php

namespace FluxErp\Http\Requests;

use FluxErp\Models\Client;
use FluxErp\Models\User;
use FluxErp\Rules\ModelExists;

class EditUserClientRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                new ModelExists(User::class),
            ],
            'clients' => 'present|array',
            'clients.*' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}
