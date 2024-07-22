<?php

namespace FluxErp\Actions\Token;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Token;
use FluxErp\Rulesets\Token\DeleteTokenRuleset;

class DeleteToken extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteTokenRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Token::class];
    }

    public function performAction(): ?bool
    {
        return app(Token::class)->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
