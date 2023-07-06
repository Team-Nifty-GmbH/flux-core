<?php

namespace FluxErp\Actions\Permission;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\EditUserPermissionRequest;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Validator;

class UpdateUserPermissions implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = array_merge(['give' => true], $data);
        $this->rules = (new EditUserPermissionRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'user.update-permissions';
    }

    public static function description(): string|null
    {
        return 'update user permissions';
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function execute(): array
    {
        $user = User::query()
            ->whereKey($this->data['user_id'])
            ->first();

        if ($this->data['give']) {
            $user->givePermissionTo($this->data['permissions']);
        } else {
            foreach ($this->data['permissions'] as $permission) {
                $user->revokePermissionTo($permission);
            }
        }

        return $user->permissions->toArray();
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
