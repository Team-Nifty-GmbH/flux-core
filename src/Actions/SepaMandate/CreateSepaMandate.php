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
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(CreateSepaMandateRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [SepaMandate::class];
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
            ->whereKey($this->data['contact_id'])
            ->where('client_id', $this->data['client_id'])
            ->exists();

        $errors = [];
        if (! $clientContactExists) {
            $errors[] = ['contact_id' => [__('Client has no such contact')]];
        }

        $contactBankConnectionExists = resolve_static(ContactBankConnection::class, 'query')
            ->whereKey($this->data['contact_bank_connection_id'])
            ->where('contact_id', $this->data['contact_id'])
            ->exists();

        if (! $contactBankConnectionExists) {
            $errors[] = ['bank_connection_id' => [__('Contact has no such bank connection')]];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('createSepaMandate');
        }
    }
}
