<?php

namespace FluxErp\Actions\Country;

use FluxErp\Contracts\ActionInterface;
use FluxErp\Models\Country;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DeleteCountry implements ActionInterface
{
    private array $data;

    private array $rules;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->rules = [
            'id' => 'required|integer|exists:countries,id,deleted_at,NULL',
        ];
    }

    public static function make(array $data): static
    {
        return (new static($data));
    }

    public static function name(): string
    {
        return 'country.delete';
    }

    public static function description(): string|null
    {
        return 'delete country';
    }

    public static function models(): array
    {
        return [Country::class];
    }

    public function execute()
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

    public function setRules(array $rules): static
    {
        $this->rules = $rules;

        return $this;
    }

    public function validate(): static
    {
        $this->data = Validator::validate($this->data, $this->rules);

        $errors = [];
        $country = Country::query()
            ->whereKey($this->data['id'])
            ->first();

        if ($country->addresses()->exists()) {
            $errors += [
                'address' => [__('Country referenced by an address')]
            ];
        }

        if ($country->clients()->exists()) {
            $errors += [
                'client' => [__('Country referenced by a client')]
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors)->errorBag('deleteCountry');
        }

        return $this;
    }
}
