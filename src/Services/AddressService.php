<?php

namespace FluxErp\Services;

use FluxErp\Helpers\ResponseHelper;
use FluxErp\Helpers\ValidationHelper;
use FluxErp\Http\Requests\UpdateAddressRequest;
use FluxErp\Models\Address;
use FluxErp\Models\AddressType;
use FluxErp\Models\Contact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class AddressService
{
    public function create(array $data): Address
    {
        $contact = Contact::query()
            ->whereKey($data['contact_id'])
            ->first();

        $mainAddress = $contact->addresses()
            ->where('is_main_address', true)
            ->first();

        $data['is_main_address'] ??= false;
        if ($data['is_main_address'] && $mainAddress) {
            $mainAddress->is_main_address = false;
            $mainAddress->save();
        } elseif (! $data['is_main_address'] && ! $mainAddress) {
            $data['is_main_address'] = true;
        }

        $contactOptions = Arr::pull($data, 'contact_options', []);

        $address = new Address($data);
        $address->save();

        if ($contactOptions) {
            $address->contactOptions()->createMany($contactOptions);
        }

        if ($data['address_types'] ?? false) {
            $addressTypes = AddressType::query()
                ->whereIntegerInRaw('id', $data['address_types'])
                ->where('is_unique', true)
                ->whereHas('addresses', function (Builder $query) use ($data) {
                    return $query->where('contact_id', $data['contact_id']);
                })
                ->get();

            foreach ($addressTypes as $addressType) {
                $addressType->addresses()->detach($address->contact->addresses->pluck('id')->toArray());
            }

            foreach ($data['address_types'] as $addressType) {
                $address->addressTypes()->attach($addressType);
            }
        }

        return $address;
    }

    public function update(array $data): array
    {
        if (! array_is_list($data)) {
            $data = [$data];
        }

        $responses = ValidationHelper::validateBulkData(
            data: $data,
            formRequest: new UpdateAddressRequest(),
            service: $this
        );

        foreach ($data as $item) {
            $address = Address::query()
                ->whereKey($item['id'])
                ->first();

            $canLogin = $address->can_login;

            $contact = $address->contact;

            $mainAddress = $contact->addresses()
                ->where('addresses.id', '!=', $address->id)
                ->where('is_main_address', true)
                ->first();

            $item['is_main_address'] ??= false;
            if ($item['is_main_address'] && $mainAddress) {
                $mainAddress->is_main_address = false;
                $mainAddress->save();
            } elseif (! $item['is_main_address'] && ! $mainAddress) {
                $validated['is_main_address'] = true;
            }

            $contactOptions = Arr::pull($item, 'contact_options', []);

            $address->fill($item);
            $address->save();

            if ($contactOptions) {
                // TODO: Update instead of delete and create
                $address->contactOptions()->delete();
                $address->contactOptions()->createMany($contactOptions);
            }

            if ($item['address_types'] ?? false) {
                $addressTypes = AddressType::query()
                    ->whereIntegerInRaw('id', $item['address_types'])
                    ->where('is_unique', true)
                    ->whereHas('addresses', function (Builder $query) use ($item) {
                        return $query->where('contact_id', $item['contact_id'])
                            ->where('id', '!=', $item['id']);
                    })
                    ->get();

                foreach ($addressTypes as $addressType) {
                    $addressType->addresses()->detach($address->contact->addresses()->get());
                }

                $address->addressTypes()->sync($item['address_types']);
            }

            if ($canLogin && ! $address->can_login) {
                $address->tokens()->delete();
            }

            $responses[] = ResponseHelper::createArrayResponse(
                statusCode: 200,
                data: $address->withoutRelations()->fresh(),
                additions: ['id' => $address->id]
            );
        }

        $statusCode = count($responses) === count($data) ? 200 : (count($data) < 1 ? 422 : 207);

        return ResponseHelper::createArrayResponse(
            statusCode: $statusCode,
            data: $responses,
            statusMessage: $statusCode === 422 ? null : 'addresses updated',
            bulk: true
        );
    }

    public function delete(string $id): array
    {
        $address = Address::query()
            ->whereKey($id)
            ->first();

        if (! $address) {
            return ResponseHelper::createArrayResponse(
                statusCode: 404,
                data: ['id' => 'address not found']
            );
        }

        $address->addressTypes()->detach();

        $address->tokens()->delete();
        $address->delete();

        return ResponseHelper::createArrayResponse(
            statusCode: 204,
            statusMessage: 'address deleted'
        );
    }

    public function validateItem(array $item, array $response): ?array
    {
        $address = Address::query()
            ->whereKey($item['id'])
            ->first();

        if ($address->can_login && ($item['can_login'] ?? false)) {
            if ($address->login_name && array_key_exists('login_name', $item) && empty($item['login_name'])) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 409,
                    data: ['login_name' => 'unable to clear login name while \'can_login\' = \'true\''],
                    additions: $response
                );
            }

            if ($address->login_password &&
                array_key_exists('login_password', $item) &&
                empty($item['login_password'])) {
                return ResponseHelper::createArrayResponse(
                    statusCode: 409,
                    data: ['login_password' => 'unable to clear login password while \'can_login\' = \'true\''],
                    additions: $response
                );
            }
        }

        return null;
    }
}
