<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\FluxAction;
use FluxErp\Http\Requests\UpdateAddressRequest;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateAddress extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = (new UpdateAddressRequest())->rules();
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function performAction(): Model
    {
        $address = Address::query()
            ->whereKey($this->data['id'])
            ->first();

        $canLogin = $address->can_login;

        if (! data_get($this->data, 'is_main_address', false)
            && ! Address::query()
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_main_address', true)
                ->exists()
        ) {
            $this->data['is_main_address'] = true;
        }

        if (! data_get($this->data, 'is_invoice_address', false)
            && ! Address::query()
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_invoice_address', true)
                ->exists()
        ) {
            $this->data['is_invoice_address'] = true;
        }

        if (! data_get($this->data, 'is_delivery_address', false)
            && ! Address::query()
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_delivery_address', true)
                ->exists()
        ) {
            $this->data['is_delivery_address'] = true;
        }

        $contactOptions = Arr::pull($this->data, 'contact_options', null);

        $address->fill($this->data);
        $address->save();

        if (! is_null($contactOptions)) {
            // TODO: Update instead of delete and create
            $address->contactOptions()->delete();
            $address->contactOptions()->createMany($contactOptions);
        }

        if ($this->data['address_types'] ?? false) {
            $addressTypes = AddressType::query()
                ->whereIntegerInRaw('id', $this->data['address_types'])
                ->where('is_unique', true)
                ->whereHas('addresses', fn (Builder $query) => $query->where('contact_id', $this->data['contact_id'])
                    ->where('id', '!=', $this->data['id'])
                )
                ->get();

            foreach ($addressTypes as $addressType) {
                $addressType->addresses()->detach($address->contact->addresses()->get());
            }

            $address->addressTypes()->sync($this->data['address_types']);
        }

        if ($canLogin && ! $address->can_login) {
            $address->tokens()->delete();
        }

        return $address->withoutRelations()->fresh();
    }

    public function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Address());

        $this->data = $validator->validate();

        $errors = [];
        $address = Address::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($address->can_login && ($this->data['can_login'] ?? false)) {
            if ($address->login_name
                && array_key_exists('login_name', $this->data)
                && ! $this->data['login_name']
            ) {
                $errors += [
                    'login_name' => [__('Unable to clear login name while \'can_login\' = \'true\'')],
                ];
            }

            if ($address->login_password &&
                array_key_exists('login_password', $this->data) &&
                ! $this->data['login_password']
            ) {
                $errors += [
                    'login_password' => [__('Unable to clear login password while \'can_login\' = \'true\'')],
                ];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateAddress');
        }
    }
}
