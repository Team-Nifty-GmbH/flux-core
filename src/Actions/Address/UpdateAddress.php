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
        $address = app(Address::class)->query()
            ->whereKey($this->data['id'])
            ->first();

        $tags = Arr::pull($this->data, 'tags');
        $contactOptions = Arr::pull($this->data, 'contact_options');

        $canLogin = $address->can_login;

        if (! data_get($this->data, 'is_main_address', false)
            && ! app(Address::class)->query()
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_main_address', true)
                ->exists()
        ) {
            $this->data['is_main_address'] = true;
        }

        if (! data_get($this->data, 'is_invoice_address', false)
            && ! app(Address::class)->query()
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_invoice_address', true)
                ->exists()
        ) {
            $this->data['is_invoice_address'] = true;
        }

        if (! data_get($this->data, 'is_delivery_address', false)
            && ! app(Address::class)->query()
                ->where('contact_id', $this->data['contact_id'] ?? $address->contact_id)
                ->where('is_delivery_address', true)
                ->exists()
        ) {
            $this->data['is_delivery_address'] = true;
        }

        if (is_null(data_get($this->data, 'login_password'))) {
            unset($this->data['login_password']);
        }

        $address->fill($this->data);
        $address->save();

        if (! is_null($tags)) {
            $address->syncTags(app(Tag::class)->query()->whereIntegerInRaw('id', $tags)->get());
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
            $addressTypes = app(AddressType::class)->query()
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

    protected function validateData(): void
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(app(Address::class));

        $this->data = $validator->validate();

        $errors = [];
        $address = app(Address::class)->query()
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
