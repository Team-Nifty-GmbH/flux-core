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
    public static function models(): array
    {
        return [User::class];
    }

    protected function getRulesets(): string|array
    {
        return UpdateUserRuleset::class;
    }

    public function performAction(): Model
    {
        $mailAccounts = Arr::pull($this->data, 'mail_accounts');
        $printers = Arr::pull($this->data, 'printers');

        $user = resolve_static(User::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $user->fill($this->data);
        $user->save();

        if (! is_null($mailAccounts)) {
            $user->mailAccounts()->sync($mailAccounts);
        }

        if (! is_null($printers)) {
            $user->printers()->sync($printers);
        }

        // Delete all tokens of the user if the user is set to is_active = false
        if ($this->getData('is_active') === false) {
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

        if (array_key_exists('password', $this->data) && is_null($this->getData('password'))) {
            unset($this->data['password']);
        }
    }

    protected function validateData(): void
    {
        if ($this->getData('termination_date')) {
            $user = resolve_static(User::class, 'query')
                ->whereKey($this->data['id'])
                ->first();

            $this->data['employment_date'] ??= $user->employment_date;
            $this->data['termination_date'] ??= $user->termination_date;
        }

        parent::validateData();

        if ($this->getData('parent_id')) {
            $user ??= resolve_static(User::class, 'query')
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
