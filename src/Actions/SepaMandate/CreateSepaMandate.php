<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use FluxErp\Rulesets\SepaMandate\CreateSepaMandateRuleset;
use Illuminate\Validation\ValidationException;

class CreateSepaMandate extends FluxAction
{
    public static function models(): array
    {
        return [SepaMandate::class];
    }

    protected function getRulesets(): string|array
    {
        return CreateSepaMandateRuleset::class;
    }

    public function performAction(): SepaMandate
    {
        $sepaMandate = app(SepaMandate::class, ['attributes' => $this->data]);
        $sepaMandate->save();

        return $sepaMandate->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $clientContactExists = resolve_static(Contact::class, 'query')
            ->whereKey($this->getData('contact_id'))
            ->where('client_id', $this->getData('client_id'))
            ->exists();

        $errors = [];
        if (! $clientContactExists) {
            $errors[] = ['contact_id' => ['Client has no such contact']];
        }

        if ($bankConnectionId = $this->getData('contact_bank_connection_id')) {
            $contactBankConnectionExists = resolve_static(ContactBankConnection::class, 'query')
                ->whereKey($bankConnectionId)
                ->where('contact_id', $this->getData('contact_id'))
                ->exists();

            if (! $contactBankConnectionExists) {
                $errors[] = ['bank_connection_id' => ['Contact has no such bank connection']];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('createSepaMandate');
        }
    }
}
