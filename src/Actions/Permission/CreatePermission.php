<?php

namespace FluxErp\Actions\Permission;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Permission;
use FluxErp\Rulesets\Permission\CreatePermissionRuleset;
use Illuminate\Database\Eloquent\Model;

class CreatePermission extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreatePermissionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Permission::class];
    }

    public function performAction(): Model
    {
        return resolve_static(Permission::class, 'create', [$this->data]);
    }

    protected function prepareForValidation(): void
    {
        $this->data['guard_name'] = $this->data['guard_name'] ?? array_keys(config('auth.guards'))[0];
    }
}
