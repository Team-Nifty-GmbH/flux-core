<?php

namespace FluxErp\Actions\Country;

use FluxErp\Actions\FluxAction;
use FluxErp\Models\Country;
use FluxErp\Rulesets\Country\DeleteCountryRuleset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeleteCountry extends FluxAction
{
    protected function getRulesets(): string|array
    {
        return DeleteCountryRuleset::class;
    }

    public static function models(): array
    {
        return [Country::class];
    }

    public function performAction(): ?bool
    {
        $country = resolve_static(Country::class, 'query')
            ->whereKey($this->data['id'])
            ->first();

        // Also delete all child country regions.
        $country->regions()->delete();

        // Rename unique columns on soft-delete.
        $country->iso_alpha2 = $country->iso_alpha2 . '___' . Hash::make(Str::uuid());
        $country->save();

        return $country->delete();
    }

    protected function validateData(): void
    {
        parent::validateData();

        $errors = [];
        $country = resolve_static(Country::class, 'query')
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
    }
}
