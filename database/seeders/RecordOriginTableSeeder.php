<?php

namespace FluxErp\Database\Seeders;

use FluxErp\Models\RecordOrigin;
use FluxErp\Traits\HasRecordOrigin;
use Illuminate\Database\Seeder;

class RecordOriginTableSeeder extends Seeder
{
    public function run(): void
    {
        $modelTypes = get_models_with_trait(HasRecordOrigin::class, fn ($class, $alias) => $alias);
        foreach ($modelTypes as $modelType) {
            RecordOrigin::factory()->count(5)->create([
                'model_type' => $modelType,
            ]);
        }
    }
}
