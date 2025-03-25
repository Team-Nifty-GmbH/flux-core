<?php

namespace FluxErp\Rulesets\Token;

use FluxErp\Models\Permission;
use FluxErp\Models\Token;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;

class CreateTokenRuleset extends FluxRuleset
{
    protected static ?string $model = Token::class;

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url',
            'abilities' => 'nullable|array',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'permissions' => 'array|nullable',
            'permissions.*' => [
                'required',
                'integer',
                app(ModelExists::class, ['model' => Permission::class]),
            ],
        ];
    }
}
