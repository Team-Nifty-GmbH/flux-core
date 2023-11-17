<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateContactBankConnectionRequest;
use FluxErp\Models\ContactBankConnection;
use Illuminate\Database\Eloquent\Model;

class UpdateContactBankConnection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateContactBankConnectionRequest())->rules();
    }

    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    public function performAction(): Model
    {
        $contactBankConnection = ContactBankConnection::query()
            ->whereKey($this->data['id'])
            ->first();

        $contactBankConnection->fill($this->data);
        $contactBankConnection->save();

        return $contactBankConnection->withoutRelations()->fresh();
    }
}
