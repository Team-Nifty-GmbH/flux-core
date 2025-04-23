<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\Media;
use FluxErp\Models\Printer;
use FluxErp\Models\PrintJob;
use FluxErp\Models\User;
use Illuminate\Database\Seeder;

class PrintJobTableSeeder extends Seeder
{
    public function run(): void
    {
        $mediaIds = Media::query()->get('id');
        $printerIds = Printer::query()->get('id');
        $userIds = User::query()->get('id');

        PrintJob::factory()->count(15)->create([
            'media_id' => fn () => $mediaIds->random()->getKey(),
            'printer_id' => fn () => faker()->boolean(75) ? $printerIds->random()->getKey() : null,
            'user_id' => fn () => faker()->boolean(75) ? $userIds->random()->getKey() : null,
        ]);
    }
}
