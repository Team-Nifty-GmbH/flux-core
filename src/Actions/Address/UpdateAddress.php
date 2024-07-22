<?php

namespace FluxErp\Actions\Address;

use FluxErp\Actions\ContactOption\CreateContactOption;
use FluxErp\Actions\ContactOption\DeleteContactOption;
use FluxErp\Actions\ContactOption\UpdateContactOption;
use FluxErp\Actions\FluxAction;
use FluxErp\Helpers\Helper;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Tag;
use FluxErp\Rulesets\Address\UpdateAddressRuleset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateAddress extends FluxAction
{
    protected function boot(array $data): void
    {
        parent::boot($data);
        $this->rules = resolve_static(UpdateAddressRuleset::class, 'getRules');
    }

    public static function models(): array
    {
        return [Address::class];
    }

    public function performAction(): Model
    {
        $address = resolve_static(Address::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        $tags = Arr::pull($this->data, 'tags');
        $permissions = Arr::pull($this->data, 'permissions');
        $contactOptions = Arr::pull($this->data, 'contact_options');

        $canLogin = $address->can_login;

        if (! data_get($this->data, 'is_main_address', false)
            && ! resolve_static(Address::class, 'query')
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_main_address', true)
                ->exists()
        ) {
            $this->data['is_main_address'] = true;
        }

        if (! data_get($this->data, 'is_invoice_address', false)
            && ! resolve_static(Address::class, 'query')
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_invoice_address', true)
                ->exists()
        ) {
            $this->data['is_invoice_address'] = true;
        }

        if (! data_get($this->data, 'is_delivery_address', false)
            && ! resolve_static(Address::class, 'query')
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_delivery_address', true)
                ->exists()
        ) {
            $this->data['is_delivery_address'] = true;
        }

        if (! trim(data_get($this->data, 'password', ''))) {
            unset($this->data['password']);
        }

        $address->fill($this->data);
        $address->save();

        if (! is_null($tags)) {
            $address->syncTags(resolve_static(Tag::class, 'query')->whereIntegerInRaw('id', $tags)->get());
        }

        if (! is_null($permissions)) {
            $address->syncPermissions($permissions);
        }

        if (! is_null($contactOptions)) {
            Helper::updateRelatedRecords(
                model: $address,
                related: $contactOptions,
                relation: 'contactOptions',
                foreignKey: 'address_id',
                createAction: CreateContactOption::class,
                updateAction: UpdateContactOption::class,
                deleteAction: DeleteContactOption::class
            );
        }

        if ($this->data['address_types'] ?? false) {
            $addressTypes = resolve_static(AddressType::class, 'query')
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

    protected function prepareForValidation(): void
    {
        $this->rules['email'][] = Rule::unique('addresses', 'email')
            ->whereNull('deleted_at')
            ->ignore(data_get($this->data, 'id'));
    }

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Address::class));

        $this->data = $validator->validate();

        $errors = [];
        $address = resolve_static(Address::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        if ($address->can_login && ($this->data['can_login'] ?? false)) {
            if ($address->email
                && array_key_exists('email', $this->data)
                && ! $this->data['email']
            ) {
                $errors += [
                    'email' => [__('Unable to clear email while \'can_login\' = \'true\'')],
                ];
            }

            if ($address->password
                && array_key_exists('password', $this->data)
                && ! $this->data['password']
            ) {
                $errors += [
                    'password' => [__('Unable to clear password while \'can_login\' = \'true\'')],
                ];
            }

            if (data_get($this->data, 'password') && ! data_get($this->data, 'email', $address->email)) {
                $errors += [
                    'email' => [__('Email is required when setting a password')],
                ];
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('updateAddress');
        }
    }
}
