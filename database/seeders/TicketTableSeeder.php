<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Address;
use FluxErp\Models\Ticket;
use FluxErp\Models\TicketType;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class TicketTableSeeder extends Seeder
{
    public function run(): void
    {
        $ticketTypes = TicketType::all(['id']);
        $addressUsers = Address::query()
            ->whereNotNull('email')
            ->whereNotNull('password')
            ->where('can_login', true)
            ->where('is_active', true)
            ->get();

        $users = $addressUsers->merge(
            User::query()
                ->where('is_active', true)
                ->get()
        );

        for ($i = 0; $i < 20; $i++) {
            Ticket::factory()->create(function () use ($users, $ticketTypes) {
                $user = $users->random();

                return [
                    'authenticatable_type' => $user->getMorphClass(),
                    'authenticatable_id' => $user->id,
                    'ticket_type_id' => rand(0, 1) ?
                        ($ticketTypes->isNotEmpty() ? $ticketTypes->random()->id : null) :
                        null,
                ];
            });
        }
    }
}
