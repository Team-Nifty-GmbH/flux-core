<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateUserRequest;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Model;

class UpdateUser extends BaseAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateUserRequest())->rules();
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function performAction(): Model
    {
        $user = User::query()
            ->whereKey($this->data['id'])
            ->first();

        $user->fill($this->data);
        $user->save();

        // Delete all tokens of the user if the user is set to is_active = false
        if (! ($this->data['is_active'] ?? true)) {
            $user->tokens()->delete();
            $user->locks()->delete();
        }

        return $user->withoutRelations()->fresh();
    }
}
