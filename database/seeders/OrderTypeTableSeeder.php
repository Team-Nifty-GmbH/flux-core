<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\OrderType;
use FluxErp\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderTypeTableSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::all(['id']);

        foreach ($tenants as $tenant) {
            foreach (OrderTypeEnum::cases() as $orderType) {
                OrderType::factory()->create([
                    'name' => Str::headline($orderType->name),
                    'tenant_id' => $tenant->id,
                    'print_layouts' => match ($orderType) {
                        OrderTypeEnum::Order, OrderTypeEnum::SplitOrder, OrderTypeEnum::Subscription => [
                            'offer',
                            'invoice',
                            'order-confirmation',
                        ],
                        OrderTypeEnum::Retoure => ['retoure'],
                        default => [],
                    },
                    'order_type_enum' => $orderType,
                ]);
            }
        }
    }
}
