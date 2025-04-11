<?php

namespace FluxErp\Actions\Token;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Token;
use FluxErp\Rulesets\Token\CreateTokenRuleset;
use Illuminate\Support\Arr;

class CreateToken extends FluxAction
{
    public static function models(): array
    {
        return [Token::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateTokenRuleset::class;
    }

    public function performAction(): Token
    {
        $permissions = Arr::pull($this->data, 'permissions');

        $token = app(Token::class, ['attributes' => $this->data]);
        $token->save();

        if ($permissions) {
            $token->givePermissionTo($permissions);
        }

        $plainTextToken = $token->createToken(
            $this->getData('name'),
            $this->getData('abilities') ?? ['*'],
            $this->getData('expires_at')
        )->plainTextToken;

        $token = $token->fresh();
        $token->token = $plainTextToken;

        return $token;
    }
}
