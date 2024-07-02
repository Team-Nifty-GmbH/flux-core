<?php

namespace FluxErp\Actions\Cart;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Cart;
use FluxErp\Rulesets\Cart\DeleteCartRuleset;

class DeleteCart extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(DeleteCartRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Cart::class];
    }

    public function performAction(): mixed
    {
        return app(Cart::class)
            ->query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }
}
