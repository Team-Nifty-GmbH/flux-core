<?php

namespace FluxErp\Actions\Role;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateRoleRequest;
use FluxErp\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class CreateRole implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateRoleRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'role.create';
    }

    public static function description(): string|null
    {
        return 'create role';
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function execute(): Model
    {
        $role = Role::create($this->data);

        if ($this->data['permissions'] ?? false) {
            $role->givePermissionTo($this->data['permissions']);
        }

        return $role;
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
