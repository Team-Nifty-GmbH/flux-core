<?php

namespace FluxErp\Actions\Role;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Role;
use FluxErp\Rulesets\Role\UpdateRoleRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateRole extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateRoleRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function performAction(): Model
    {
        $role = app(Role::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $role->fill($this->data);
        $role->save();

        if ($this->data['permissions'] ?? false) {
            $role->syncPermissions(array_map('intval', $this->data['permissions']));
        }

        return $role->withoutRelations()->fresh();
    }
}
