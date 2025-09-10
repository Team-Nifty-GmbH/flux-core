<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $i = 0;
        while (Client::query()
            ->where('client_code', $clientCode = fake()->unique()->countryISOAlpha3())
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $clientCode .= '_' . Str::uuid();
        }

        return [
            'name' => fake()->company(),
            'client_code' => $clientCode,
            'ceo' => fake()->name(),
            'street' => fake()->streetAddress(),
            'city' => fake()->city(),
            'postcode' => fake()->postcode(),
            'phone' => fake()->phoneNumber(),
            'fax' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'website' => fake()->url(),
            'is_active' => fake()->boolean(90),
        ];
    }
}
