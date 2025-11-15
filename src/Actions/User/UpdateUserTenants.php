<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\UpdateUserTenantsRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateUserTenants extends FluxAction
{
    public static function models(): array
    {
        return [User::class];
    }

    public static function name(): string
    {
        return 'user.update-tenants';
    }

    protected function getRulesets(): string|array
    {
        return UpdateUserTenantsRuleset::class;
    }

    public function performAction(): Model
    {
        $user = resolve_static(User::class, 'query')
            ->whereKey($this->data['user_id'])
            ->first();

        $user->tenants()->sync($this->data['tenants']);

        return $user;
    }
}
