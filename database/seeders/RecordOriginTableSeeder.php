<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\RecordOrigin;
use FluxErp\Traits\HasRecordOrigin;
use Illuminate\Database\Seeder;

class RecordOriginTableSeeder extends Seeder
{
    public function run(): void
    {
        RecordOrigin::factory()->count(10)->create([
            'model_type' => fn () => faker()->randomElement(
                get_models_with_trait(HasRecordOrigin::class, fn ($modelClass) => morph_alias($modelClass))
            ),
        ]);
    }
}
