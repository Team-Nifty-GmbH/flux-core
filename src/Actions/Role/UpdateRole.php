<?php

namespace FluxErp\Actions\Role;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\UpdateRoleRequest;
use FluxErp\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UpdateRole implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new UpdateRoleRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'role.update';
    }

    public static function description(): string|null
    {
        return 'update role';
    }

    public static function models(): array
    {
        return [Role::class];
    }

    public function execute(): Model
    {
        $role = Role::query()
            ->whereKey($this->data['id'])
            ->first();

        $role->fill($this->data);
        $role->save();

        if ($this->data['permissions'] ?? false) {
            $role->syncPermissions($this->data['permissions']);
        }

        return $role->withoutRelations()->fresh();
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
