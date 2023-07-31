<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateSepaMandateRequest;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\SepaMandate;
use Illuminate\Validation\ValidationException;

class CreateSepaMandate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateSepaMandateRequest())->rules();
    }

    public static function models(): array
    {
        return [SepaMandate::class];
    }

    public function performAction(): SepaMandate
    {
        $sepaMandate = new SepaMandate($this->data);
        $sepaMandate->save();

        return $sepaMandate->fresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        $clientContactExists = Contact::query()
            ->whereKey($this->data['contact_id'])
            ->where('client_id', $this->data['client_id'])
            ->exists();

        $errors = [];
        if (! $clientContactExists) {
            $errors[] = ['contact_id' => [__('Client has no such contact')]];
        }

        $contactBankConnectionExists = BankConnection::query()
            ->whereKey($this->data['bank_connection_id'])
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
