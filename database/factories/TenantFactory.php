<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $i = 0;
        while (Tenant::query()
            ->where('tenant_code', $clientCode = fake()->unique()->countryISOAlpha3())
            ->exists() && $i < 100) {
            $i++;
        }

        if ($i === 100) {
            $clientCode .= '_' . Str::uuid();
        }

        return [
            'name' => fake()->company(),
            'tenant_code' => $clientCode,
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
