<?php

namespace FluxErp\Rulesets\VacationBlackout;

use FluxErp\Models\Client;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use FluxErp\Rulesets\FluxRuleset;
use FluxErp\Rules\ModelExists;

class CreateVacationBlackoutRuleset extends FluxRuleset
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'role_ids' => 'nullable|array',
            'role_ids.*' => [
                'integer',
                new ModelExists(Role::class),
            ],
            'user_ids' => 'nullable|array',
            'user_ids.*' => [
                'integer',
                new ModelExists(User::class),
            ],
            'client_id' => [
                'required',
                'integer',
                new ModelExists(Client::class),
            ],
        ];
    }
}