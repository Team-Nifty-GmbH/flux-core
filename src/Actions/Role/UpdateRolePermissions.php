<?php

namespace FluxErp\Actions\Role;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\EditRolePermissionRequest;
use FluxErp\Models\Role;
use Illuminate\Support\Facades\Validator;

class UpdateRolePermissions implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = array_merge(['give' => true], $data);
        $this->rules = (new EditRolePermissionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'role.update-permissions';
    }

    public static function description(): string|null
    {
        return 'update role permissions';
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function execute(): array
    {
        $role = Role::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($this->data['give']) {
            $role->givePermissionTo($this->data['permissions']);
        } else {
            foreach ($this->data['permissions'] as $permission) {
                $role->revokePermissionTo($permission);
            }
        }

        return $role->permissions->toArray();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
