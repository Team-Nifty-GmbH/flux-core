<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Pivots\PrinterUser;
use FluxErp\Models\Printer;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class PrinterUserTableSeeder extends Seeder
{
    public function run(): void
    {
        $printerIds = Printer::query()->get('id');
        $cutPrinterIds = $printerIds->random(bcfloor($printerIds->count() * 0.8));

        $userIds = User::query()->get('id');
        $cutUserIds = $userIds->random(bcfloor($userIds->count() * 0.6));

        foreach ($cutUserIds as $cutUserId) {
            $numGroups = rand(1, bcfloor($cutPrinterIds->count() * 0.5));

            $ids = $cutPrinterIds->random($numGroups)->pluck('id')->toArray();

            foreach ($ids as $id) {
                PrinterUser::factory()->create([
                    'user_id' => $cutUserId,
                    'printer_id' => $id,
                ]);
            }
        }
    }
}
