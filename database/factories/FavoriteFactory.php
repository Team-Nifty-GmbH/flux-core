<?php

namespace FluxErp\Database\Factories;

use FluxErp\Models\Favorite;
use Illuminate\Database\Eloquent\Factories\Factory;

class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    public function definition(): array
    {
        $favoriteUrls = [
            '/contacts',
            '/calendars',
            '/mail',
            '/tasks',
            '/tickets',
            '/projects',
            '/accounting/work-times',
            '/accounting/commissions',
            '/accounting/payment-reminders',
            '/accounting/purchase-invoices',
            '/accounting/transactions',
        ];

        $url = $this->faker->randomElement($favoriteUrls);

        return [
            'name' => basename($url),
            'url' => $url,
        ];
    }
}
