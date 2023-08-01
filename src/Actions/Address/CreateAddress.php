<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\CreateAddressRequest;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateAddress extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new CreateAddressRequest())->rules();
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function performAction(): Address
    {
        $mainAddress = UpdateMainAddress::make([
            'address_id' => null,
            'contact_id' => $this->data['contact_id'],
            'is_main_address' => ! ($this->data['is_main_address'] ??= false),
        ])->execute();

        if (! $this->data['is_main_address'] && ! $mainAddress) {
            $this->data['is_main_address'] = true;
        }

        $contactOptions = Arr::pull($this->data, 'contact_options', []);

        $address = new Address($this->data);
        $address->save();

        if ($contactOptions) {
            $address->contactOptions()->createMany($contactOptions);
        }

        if ($this->data['address_types'] ?? false) {
            $addressTypes = AddressType::query()
                ->whereIntegerInRaw('id', $this->data['address_types'])
                ->where('is_unique', true)
                ->whereHas(
                    'addresses',
                    fn (Builder $query) => $query->where('contact_id', $this->data['contact_id'])
                )
                ->get();

            foreach ($addressTypes as $addressType) {
                $addressType->addresses()->detach($address->contact->addresses->pluck('id')->toArray());
            }

            foreach ($this->data['address_types'] as $addressType) {
                $address->addressTypes()->attach($addressType);
            }
        }

        return $address->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Address());

        $this->data = $validator->validate();
    }
}
