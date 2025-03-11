<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\UpdateUserClientsRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateUserClients extends FluxAction
{
    public static function models(): array
    {
        return [User::class];
    }

    public static function name(): string
    {
        return 'user.update-clients';
    }

    protected function getRulesets(): string|array
    {
        return UpdateUserClientsRuleset::class;
    }

    public function performAction(): Model
    {
        $user = resolve_static(User::class, 'query')
            ->whereKey($this->data['user_id'])
            ->first();

        $user->clients()->sync($this->data['clients']);

        return $user;
    }
}
