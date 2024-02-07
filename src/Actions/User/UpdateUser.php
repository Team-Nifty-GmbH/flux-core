<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Http\Requests\UpdateUserRequest;
use FluxErp\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateUser extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);

        $rules = (new UpdateUserRequest())->rules();

        $this->rules = array_merge(
            $rules,
            [
                'user_code' => $rules['user_code'] . ',' . $this->data['id'],
                'email' => $rules['email'] . ',' . $this->data['id'],
            ]
        );
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function performAction(): Model
    {
        $mailAccounts = Arr::pull($this->data, 'mail_accounts');

        $user = User::query()
            ->whereKey($this->data['id'])
            ->first();

        $user->fill($this->data);
        $user->save();

        if (! is_null($mailAccounts)) {
            $user->mailAccounts()->sync($mailAccounts);
        }

        // Delete all tokens of the user if the user is set to is_active = false
        if (! ($this->data['is_active'] ?? true)) {
            $user->tokens()->delete();
            $user->locks()->delete();
        }

        return $user->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->data['parent_id'] ?? false) {
            $user = User::query()
                ->whereKey($this->data['id'])
                ->first();

            if (Helper::checkCycle(User::class, $user, $this->data['parent_id'])) {
                throw ValidationException::withMessages([
                    'parent_id' => ['Cycle detected'],
                ])->errorBag('updateUser');
            }
        }
    }
}
