<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Address;
use FluxErp\Models\Contact;
use Illuminate\Database\Eloquent\Builder;

class UpdateMainAddress extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = [
            'address_id' => 'integer|nullable|exists:addresses,id,deleted_at,NULL',
            'contact_id' => 'required|integer|exists:contacts,id,deleted_at,NULL',
            'is_main_address' => 'required|boolean',
        ];
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function performAction(): ?Address
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

        return $address?->fresh();
    }
}
