<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DeleteUser extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:users,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function execute(): ?bool
    {
        $user = User::query()
            ->whereKey($this->data['id'])
            ->first();

        $user->tokens()->delete();
        $user->locks()->delete();

        return $user->delete();
    }

    public function validate(): static
    {
        parent::validate();

        if ($this->data['id'] == Auth::id()) {
            throw ValidationException::withMessages([
                'id' => [__('Cannot delete yourself')],
            ])->errorBag('deleteUser');
        }

        return $this;
    }
}
