<?php

namespace FluxErp\Actions\Permission;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\User;
use FluxErp\Rulesets\Permission\UpdateUserPermissionRuleset;

class UpdateUserPermissions extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->data = $this->data ? array_merge(['give' => true, 'sync' => false], $this->data) : [];
    }

    public static function name(): string
    {
        return 'user.update-permissions';
    }

    public static function models(): array
    {
        return [User::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateUserPermissionRuleset::class;
    }

    public function performAction(): array
    {
        $user = resolve_static(User::class, 'query')
            ->whereKey($this->data['user_id'])
            ->first();

        if ($this->data['sync']) {
            $user->syncPermissions($this->data['permissions']);

            return $user->permissions->toArray();
        }

        if ($this->data['give']) {
            $user->givePermissionTo($this->data['permissions']);
        } else {
            foreach ($this->data['permissions'] as $permission) {
                $user->revokePermissionTo($permission);
            }
        }

        return $user->permissions->toArray();
    }
}
