<?php

namespace FluxErp\Actions\Role;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\EditRoleUserRequest;
use FluxErp\Models\Role;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Validator;

class UpdateRoleUsers implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = array_merge(['assign' => true], $data);
        $this->rules = (new EditRoleUserRequest())->rules();
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'role.update-users';
    }

    public static function description(): string|null
    {
        return 'update role users';
    }

    public static function models(): array
    {
        return [Role::class, User::class];
    }

    public function execute(): array
    {
        $role = Role::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($this->data['assign']) {
            $role->users()->syncWithoutDetaching($this->data['users']);
        } else {
            $role->users()->detach($this->data['users']);
        }

        return $role->users->toArray();
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
