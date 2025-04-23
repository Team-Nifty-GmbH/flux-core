<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\OrderPosition;
use FluxErp\Models\Pivots\OrderPositionStockPosting;
use FluxErp\Models\Pivots\RoleTicketType;
use FluxErp\Models\Role;
use FluxErp\Models\StockPosting;
use FluxErp\Models\TicketType;
use Illuminate\Database\Seeder;

class RoleTicketTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $roleIds = Role::query()->get('id');
        $cutRoleIds = $roleIds->random(bcfloor($roleIds->count() * 0.75));
        $ticketTypeIds = TicketType::query()->get('id');
        $cutTicketTypeIds = $ticketTypeIds->random(bcfloor($ticketTypeIds->count() * 0.6));

        foreach ($cutRoleIds as $cutRoleId) {
            $numGroups = rand(1, floor($cutTicketTypeIds->count() * 0.5));

            $ids = $cutTicketTypeIds->random($numGroups)->pluck('id')->toArray();

            foreach ($ids as $id) {
                RoleTicketType::factory()->create([
                    'role_id' => $cutRoleId,
                    'ticket_type_id' => $id,
                ]);
            }
        }
    }
}
