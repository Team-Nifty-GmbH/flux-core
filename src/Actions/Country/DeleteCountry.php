<?php

namespace FluxErp\Actions\Country;

use FluxErp\Actions\BaseAction;
use FluxErp\Models\Country;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeleteCountry extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = [
            'id' => 'required|integer|exists:countries,id,deleted_at,NULL',
        ];
    }

    public static function models(): array
    {
        return [Country::class];
    }

    public function execute(): ?bool
    {
        $country = Country::query()
            ->whereKey($this->data['id'])
            ->first();

        // Also delete all child country regions.
        $country->regions()->delete();

        // Rename unique columns on soft-delete.
        $country->iso_alpha2 = $country->iso_alpha2 . '___' . Hash::make(Str::uuid());
        $country->save();

        return $country->delete();
    }

    public function validate(): static
    {
        parent::validate();

        $errors = [];
        $country = Country::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($country->addresses()->exists()) {
            $errors += [
                'address' => [__('Country referenced by an address')],
            ];
        }

        if ($country->clients()->exists()) {
            $errors += [
                'client' => [__('Country referenced by a client')],
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteCountry');
        }

        return $this;
    }
}
