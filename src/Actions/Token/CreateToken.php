<?php

namespace FluxErp\Actions\Token;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Token;
use FluxErp\Rulesets\Token\CreateTokenRuleset;

class CreateToken extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return CreateTokenRuleset::class;
    }

    public static function models(): array
    {
        return [Token::class];
    }

    public function performAction(): mixed
    {
        $token = app(Token::class, ['attributes' => $this->data]);
        $token->save();

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
