<?php

namespace FluxErp\Actions\Account;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateAccountRequest;
use FluxErp\Models\Account;

class CreateAccount extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateAccountRequest())->rules();
    }

    public static function models(): array
    {
        return [Account::class];
    }

    public function performAction(): Account
    {
        $account = new Account($this->data);
        $account->save();

        return $account;
    }
}
