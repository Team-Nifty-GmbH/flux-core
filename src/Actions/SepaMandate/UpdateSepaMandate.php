<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\ContactBankConnection;
use FluxErp\Models\SepaMandate;
use FluxErp\Rulesets\SepaMandate\UpdateSepaMandateRuleset;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class UpdateSepaMandate extends FluxAction
{
    public static function getRulesets(): string|array
    {
        return UpdateSepaMandateRuleset::class;
    }

    public static function models(): array
    {
        return [SepaMandate::class];
    }

    public function performAction(): Model
    {
        $sepaMandate = resolve_static(SepaMandate::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $sepaMandate->fill($this->data);
        $sepaMandate->save();

        return $sepaMandate->withoutRelations()->fresh();
    }

    protected function validateData(): void
    {
        parent::validateData();

        if ($this->data['contact_bank_connection_id'] ?? false) {
            $sepaMandate = resolve_static(SepaMandate::class, 'query')
                ->whereKey($this->data['id'])
                ->first();

            $contactBankConnectionExists = resolve_static(ContactBankConnection::class, 'query')
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
