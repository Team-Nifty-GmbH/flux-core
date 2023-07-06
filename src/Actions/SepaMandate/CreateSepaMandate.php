<?php

namespace FluxErp\Actions\SepaMandate;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Http\Requests\CreateSepaMandateRequest;
use FluxErp\Models\BankConnection;
use FluxErp\Models\Contact;
use FluxErp\Models\SepaMandate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateSepaMandate implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = (new CreateSepaMandateRequest())->rules();
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    public static function name(): string
    {
        return 'sepa-mandate.create';
    }

    public static function description(): string|null
    {
        return 'create sepa mandate';
    }

    public static function models(): array
    {
        return [SepaMandate::class];
    }

    public function execute(): SepaMandate
    {
        $sepaMandate = new SepaMandate($this->data);
        $sepaMandate->save();

        return $sepaMandate;
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

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

        return $this;
    }
}
