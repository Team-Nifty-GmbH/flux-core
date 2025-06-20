<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Client;
use FluxErp\Models\Contact;
use FluxErp\Models\Order;
use FluxErp\Models\Project;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class ProjectTableSeeder extends Seeder
{
    public function run(): void
    {
        $clientIds = Client::query()->get('id');
        $cutClientIds = $clientIds->random(max(1, bcfloor($clientIds->count() * 0.75)));

        $contactIds = Contact::query()->get('id');
        $cutContactIds = $contactIds->random(max(1, bcfloor($contactIds->count() * 0.75)));

        $orderIds = Order::query()->get('id');
        $cutOrderIds = $orderIds->random(max(1, bcfloor($orderIds->count() * 0.75)));

        $userIds = User::query()->get('id');
        $cutUserIds = $userIds->random(max(1, bcfloor($userIds->count() * 0.75)));

        Project::factory()->count(10)->create([
            'client_id' => fn () => $cutClientIds->random()->getKey(),
            'contact_id' => fn () => faker()->boolean() ? $cutContactIds->random()->getKey() : null,
            'order_id' => fn () => faker()->boolean() ? $cutOrderIds->random()->getKey() : null,
            'responsible_user_id' => fn () => faker()->boolean() ? $cutUserIds->random()->getKey() : null,
        ]);
    }
}
