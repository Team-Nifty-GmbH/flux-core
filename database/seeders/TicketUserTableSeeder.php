<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Ticket;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class TicketUserTableSeeder extends Seeder
{
    public function run(): void
    {
        $ticketIds = Ticket::query()->get('id');
        $cutTicketIds = $ticketIds->random(bcfloor($ticketIds->count() * 0.6));
        $userIds = User::query()->get('id');
        $cutUserIds = $userIds->random(bcfloor($userIds->count() * 0.6));

        foreach ($cutTicketIds as $cutTicketId) {
            $cutTicketId->users()->attach($cutUserIds->random(rand(1, $cutUserIds->count())));
        }
    }
}
