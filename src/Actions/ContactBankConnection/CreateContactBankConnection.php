<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateContactBankConnectionRequest;
use FluxErp\Models\ContactBankConnection;

class CreateContactBankConnection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateContactBankConnectionRequest())->rules();
    }

    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    public function performAction(): ContactBankConnection
    {
        $contactBankConnection = new ContactBankConnection($this->data);
        $contactBankConnection->save();

        return $contactBankConnection->fresh();
    }
}
