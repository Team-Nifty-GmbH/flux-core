<?php

namespace FluxErp\Actions\Account;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateAccountRequest;
use FluxErp\Models\Account;

class CreateAccount extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateAccountRequest())->rules();
    }

    public static function models(): array
    {
        return [Account::class];
    }

    public function execute(): Account
    {
        $account = new Account($this->data);
        $account->save();

        return $account;
    }
}
