<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $i = 0;
        while (Client::query()
            ->where('client_code', $clientCode = $this->faker->unique()->countryISOAlpha3())
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $clientCode .= '_' . Str::uuid();
        }

        return [
            'name' => $this->faker->company(),
            'client_code' => $clientCode,
            'ceo' => $this->faker->name(),
            'street' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'postcode' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
            'fax' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'website' => $this->faker->url(),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
