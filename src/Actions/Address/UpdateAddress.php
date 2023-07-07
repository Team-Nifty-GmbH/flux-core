<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\UpdateAddressRequest;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateAddress extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new UpdateAddressRequest())->rules();
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function execute(): Model
    {
        $address = Address::query()
            ->whereKey($this->data['id'])
            ->first();

        $canLogin = $address->can_login;

        $mainAddress = UpdateMainAddress::make([
            'address_id' => $address->id,
            'contact_id' => $address->contact_id,
            'is_main_address' => ! ($this->data['is_main_address'] ??= false),
        ])->execute();

        if (! $this->data['is_main_address'] && ! $mainAddress) {
            $this->data['is_main_address'] = true;
        }

        $contactOptions = Arr::pull($this->data, 'contact_options', []);

        $address->fill($this->data);
        $address->save();

        if ($contactOptions) {
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

    public function validate(): static
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

        return $this;
    }
}
