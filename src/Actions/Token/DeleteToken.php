<?php

namespace FluxErp\Actions\Token;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Token;
use FluxErp\Rulesets\Token\DeleteTokenRuleset;

class DeleteToken extends FluxAction
{
    public static function models(): array
    {
        return [Token::class];
    }

    protected function getRulesets(): string|array
    {
        return DeleteTokenRuleset::class;
    }

    public function performAction(): ?bool
    {
        return resolve_static(Token::class, 'query')
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
