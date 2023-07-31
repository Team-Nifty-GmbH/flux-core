<?php

namespace FluxErp\Actions\BankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateBankConnectionRequest;
use FluxErp\Models\BankConnection;

class CreateBankConnection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateBankConnectionRequest())->rules();
    }

    public static function models(): array
    {
        return [BankConnection::class];
    }

    public function performAction(): BankConnection
    {
        $bankConnection = new BankConnection($this->data);
        $bankConnection->save();

        return $bankConnection;
    }
}
