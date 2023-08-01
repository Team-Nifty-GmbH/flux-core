<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateSepaMandateRequest;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\SepaMandate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateSepaMandate extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateSepaMandateRequest())->rules();
    }

    public static function models(): array
    {
        return [SepaMandate::class];
    }

    public function performAction(): Model
    {
        $sepaMandate = SepaMandate::query()
            ->whereKey($this->data['id'])
            ->first();

        $sepaMandate->fill($this->data);
        $sepaMandate->save();

        return $sepaMandate->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        parent::validateData();

        $sepaMandate = SepaMandate::query()
            ->whereKey($this->data['id'])
            ->first();

        $this->data['contact_id'] = $this->data['contact_id'] ?? $sepaMandate->contact_id;
        $this->data['client_id'] = $this->data['client_id'] ?? $sepaMandate->client_id;
        $this->data['bank_connection_id'] = $this->data['bank_connection_id'] ??
            $sepaMandate->bank_connection_id;

        $clientContactExists = Contact::query()
            ->whereKey($this->data['contact_id'])
            ->where('client_id', $this->data['client_id'])
            ->exists();

        $contactBankConnectionExists = BankConnection::query()
            ->whereKey($this->data['bank_connection_id'])
            ->where('contact_id', $this->data['contact_id'])
            ->exists();

        $errors = [];
        if (! $clientContactExists) {
            $errors += [
                'contact_id' => ['contact with id: \'' . $this->data['contact_id'] .
                    '\' doesnt match client id:\'' . $this->data['client_id'] . '\'',
                ],
            ];
        }

        if (! $contactBankConnectionExists) {
            $errors += [
                'bank_connection_id' => ['bank connection with id: \'' .
                    $this->data['bank_connection_id'] . '\' doesnt match contact id:' . $this->data['contact_id'] . '\'',
                ],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateSepaMandate');
        }
    }
}
