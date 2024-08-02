<?php

namespace FluxErp\Actions\ContactBankConnection;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Rulesets\ContactBankConnection\UpdateContactBankConnectionRuleset;
use Illuminate\Database\Eloquent\Model;

class UpdateContactBankConnection extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateContactBankConnectionRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [ContactBankConnection::class];
    }

    public function performAction(): Model
    {
        $contactBankConnection = resolve_static(ContactBankConnection::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $contactBankConnection->fill($this->data);
        $contactBankConnection->save();

        return $contactBankConnection->withoutRelations()->fresh();
    }
}
