<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Media;
use FluxErp\Models\Order;
use FluxErp\Models\PaymentReminder;
use Illuminate\Database\Seeder;

class PaymentReminderTableSeeder extends Seeder
{
    public function run(): void
    {
        $orderIds = Order::query()->get('id');
        $cutOrderIds = $orderIds->random(bcfloor($orderIds->count() * 0.7));

        $mediaIds = Media::query()->get('id');
        $cutMediaIds = $mediaIds->random(bcfloor($mediaIds->count() * 0.7));

        PaymentReminder::factory()->count(5)->create([
            'order_id' => fn () => $cutOrderIds->random()->getKey(),
            'media_id' => fn () => fake()->boolean()
                ? $cutMediaIds->random()->getKey()
                : null,
        ]);
    }
}
