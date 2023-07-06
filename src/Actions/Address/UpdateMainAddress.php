<?php

namespace FluxErp\Actions\Address;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class UpdateMainAddress implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'address_id' => 'integer|nullable|exists:addresses,id,deleted_at,NULL',
            'contact_id' => 'required|integer|exists:contacts,id,deleted_at,NULL',
            'is_main_address' => 'required|boolean',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'address.create';
    }

    public static function description(): string|null
    {
        return 'create address';
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function execute(): Address|null
    {
        $contact = Contact::query()
            ->whereKey($this->data['contact_id'])
            ->first();

        $address = $contact->addresses()
            ->when(
                $this->data['address_id'],
                fn (Builder $query) => $query->where('addresses.id', '!=', $this->data['address_id'])
            )
            ->where('is_main_address', true)
            ->first();

        if ($address) {
            $address->is_main_address = $this->data['is_main_address'];
            $address->save();
        }

        return $address;
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
