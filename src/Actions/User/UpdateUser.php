<?php

namespace FluxErp\Actions\User;

use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\User;
use FluxErp\Rulesets\User\UpdateUserRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UpdateUser extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateUserRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [User::class];
    }

    public function performAction(): Model
    {
        $mailAccounts = Arr::pull($this->data, 'mail_accounts');

        $user = resolve_static(User::class, 'query')
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

    protected function prepareForValidation(): void
    {
        $this->rules = array_merge(
            $this->rules,
            [
                'user_code' => $this->rules['user_code'] . ',' . ($this->data['id'] ?? 0),
                'email' => $this->rules['email'] . ',' . ($this->data['id'] ?? 0),
            ]
        );
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->data['parent_id'] ?? false) {
            $user = resolve_static(User::class, 'query')
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
