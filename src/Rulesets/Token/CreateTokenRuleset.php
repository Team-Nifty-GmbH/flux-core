<?php

namespace FluxErp\Rulesets\Token;

use FluxErp\Models\Permission;
use FluxErp\Models\Token;
use FluxErp\Rules\ModelExists;
use FluxErp\Rulesets\FluxRuleset;
use Illuminate\Validation\Rule;

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
            'abilities.*' => [
                'required',
                'string',
                Rule::in([
                    '*',
                    'interface',
                    'user',
                    'address',
                ]),
            ],
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
