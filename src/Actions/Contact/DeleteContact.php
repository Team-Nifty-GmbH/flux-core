<?php

namespace FluxErp\Actions\Contact;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Contact;
use Illuminate\Support\Facades\Validator;

class DeleteContact implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:contacts,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'contact.delete';
    }

    public static function description(): string|null
    {
        return 'delete contact';
    }

    public static function models(): array
    {
        return [Contact::class];
    }

    public function execute()
    {
        return Contact::query()
            ->whereKey($this->data['id'])
            ->first()
            ->delete();
    }

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        return $this;
    }
}
