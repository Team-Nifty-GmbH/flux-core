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
        $printerIds = Printer::query()->pluck('id');
        $cutPrinterIds = $printerIds->random(bcfloor($printerIds->count() * 0.8));

        $userIds = User::query()->pluck('id');
        $cutUserIds = $userIds->random(bcfloor($userIds->count() * 0.6));

        foreach ($cutUserIds as $userId) {
            $numGroups = rand(1, bcfloor($cutPrinterIds->count() * 0.5));

            $selectedPrinterIds = $cutPrinterIds->random($numGroups);

            foreach ($selectedPrinterIds as $printerId) {
                PrinterUser::create([
                    'user_id' => $userId,
                    'printer_id' => $printerId,
                ]);
            }
        }
    }
}
