<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateSepaMandateRequest;
use FluxErp\Models\Contact;
use FluxErp\Models\ContactBankConnection;
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

        if ($this->data['contact_bank_connection_id'] ?? false) {
            $sepaMandate = SepaMandate::query()
                ->whereKey($this->data['id'])
                ->first();

            $contactBankConnectionExists = ContactBankConnection::query()
                ->whereKey($this->data['contact_bank_connection_id'])
                ->where('contact_id', $sepaMandate->contact_id)
                ->exists();

            if (! $contactBankConnectionExists) {
                throw ValidationException::withMessages([
                    'contact_bank_connection_id' => ['contact bank connection does not exist on contact.'],
                ])->errorBag('updateSepaMandate');
            }
        }
    }
}
