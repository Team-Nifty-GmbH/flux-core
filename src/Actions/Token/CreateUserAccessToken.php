<?php

namespace FluxErp\Actions\Token;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\User;
use FluxErp\Rulesets\Token\CreateUserAccessTokenRuleset;

class CreateUserAccessToken extends FluxAction
{
    protected static bool $hasPermission = false;

    public static function models(): array
    {
        return [User::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateUserAccessTokenRuleset::class;
    }

    public function performAction(): array
    {
        $token = auth()->user()->createToken(
            $this->getData('name'),
            $this->getData('abilities') ?? ['user'],
        );

        return ['token' => $token->plainTextToken];
    }
}
