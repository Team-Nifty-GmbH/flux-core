<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Pivots\RoleTicketType;
use FluxErp\Models\Role;
use FluxErp\Models\TicketType;
use Illuminate\Database\Seeder;

class RoleTicketTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $roleIds = Role::query()->pluck('id');
        $cutRoleIds = $roleIds->random(bcfloor($roleIds->count() * 0.75));

        $ticketTypeIds = TicketType::query()->pluck('id');
        $cutTicketTypeIds = $ticketTypeIds->random(bcfloor($ticketTypeIds->count() * 0.6));

        foreach ($cutRoleIds as $roleId) {
            $numGroups = rand(1, floor($cutTicketTypeIds->count() * 0.5));

            $selectedTicketTypeIds = $cutTicketTypeIds->random($numGroups);

            foreach ($selectedTicketTypeIds as $ticketTypeId) {
                RoleTicketType::create([
                    'role_id' => $roleId,
                    'ticket_type_id' => $ticketTypeId,
                ]);
            }
        }
    }
}
